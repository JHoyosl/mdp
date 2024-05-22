<?php

namespace App\Services\Account;

use Exception;
use App\Models\Account;
use App\Models\Company;
use App\Models\MapFile;
use App\Traits\DatesTrait;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Models\AccountingItems;
use Illuminate\Support\Facades\DB;
use App\Models\HeaderAccountingInfo;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use App\Services\MappingFile\MappingFileService;

class AccountingService
{
    use DatesTrait;

    private MappingFileService $mappingFileService;
    protected $accountingItemsTable = '';

    public function __construct(MappingFileService $mappingFileService)
    {
        $this->mappingFileService = $mappingFileService;
    }

    public function getAccInfoToReconciliate($companyId, $localAccount, $startDate, $endDate)
    {
        $tableName  = $this->getAccountinItemsTableName($companyId);
        $tableAccItems = new AccountingItems($tableName);
        $info  = $tableAccItems
            ->where('local_account', $localAccount)
            ->whereBetween('fecha_movimiento', [$startDate, $endDate])
            ->get();

        return $info;
    }

    public function getAccHeadersByDate($companyId, $date)
    {
        $header  = HeaderAccountingInfo::where('company_id', $companyId)
            ->where('start_date', '<=', $date)
            ->orderBy('end_date', 'ASC')
            ->get();

        return $header;
    }

    public function getLastHeader($companyId)
    {
        $header  = HeaderAccountingInfo::where('company_id', $companyId)
            ->orderBy('end_date', 'ASC')
            ->first();

        return $header;
    }

    public function index($companyId)
    {
        return HeaderAccountingInfo::where('company_id', $companyId)->get();
    }

    public function uploadAccountInfo($user, $file, $startDate, $endDate, $company)
    {
        ini_set('memory_limit', '-1');
        set_time_limit(300);

        $this->accountingItemsTable = $this->getAccountinItemsTableName($user->current_company);

        $this->createTableAccountingItems();

        $accounts = Account::where('company_id', $company->id)->get();
        $accArray = [];
        foreach ($accounts as $account) {
            $accArray[$account->local_account] = $account->bank_account;
        }

        DB::beginTransaction();

        try {

            $newHeader = $this->createAccountingHeaderInfo(
                $user->current_company,
                $user->id,
                $file,
                $startDate,
                $endDate
            );

            $mapped = $this->getInsertConciliarLocal($file, $company->map_id, $startDate, $endDate, $newHeader->id, $accArray);

            $newHeader->rows = count($mapped);
            $newHeader->save();

            foreach (array_chunk($mapped, 500) as $t) {
                DB::table($this->accountingItemsTable)->insert($t);
            }
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
        DB::commit();
        return $newHeader;
    }

    private function createAccountingHeaderInfo($companyId, $userId, $file, $startDate, $endDate)
    {
        $this->dateValidation($startDate, $companyId);

        //set accouting path
        $path = $companyId . '/accounting/';

        $lastHeader = HeaderAccountingInfo::where('status', HeaderAccountingInfo::STATUS_CREATED)->first();

        if ($lastHeader) {
            throw new Exception("TODO: Mensje de exception");
        }
        // check if company folder exist
        if (!is_dir(storage_path($path))) {

            mkdir(storage_path($path), 0775, true);
        }

        $storedPath = Storage::disk('accounting')->put($path, $file);

        $headerInfo = [
            'id' => Str::uuid(),
            'company_id' => $companyId,
            'uploaded_by' => $userId,
            'path' => $storedPath,
            'file_name' => $file->getClientOriginalName(),
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];

        $newHeader = HeaderAccountingInfo::create($headerInfo);

        return $newHeader;
    }

    private function dateValidation($startDate, $companyId)
    {
        $lastHeader = HeaderAccountingInfo::where('status', 'OPEN')
            ->where('company_id', $companyId)
            ->orderBy('created_at', 'DESC')
            ->first();

        if ($lastHeader) {
            $headerDate = Carbon::parse($lastHeader->end_date);

            $nextDay = $headerDate->addDay(1);

            //check if upload is consecutive
            if ($nextDay->ne($startDate)) {
                throw new Exception("El cargue debe comenzar en {$nextDay->format('Y-m-d')} y el actual es {$startDate}");
            }
        }
    }

    public function getAccountById(String $id)
    {

        $account = Account::findOrFail($id);
        return $account;
    }

    public function getInsertConciliarLocal($file, $map_id, $startDate, $endDate, $headerId, $accArray)
    {
        //Garantee miss match date with time
        $carbonStart = Carbon::parse($startDate)->subDay();
        $carbonEnd = Carbon::parse($endDate)->addDay();

        $fileArray = $this->fileToArray($file);
        $mapIndex = $this->mappingFileService->getMapIndex(MapFile::TYPE_INTERNAL);
        $mapModel = MapFile::find($map_id);
        $map = json_decode($mapModel->map, true);
        $separator = $mapModel->separator;
        $dateFormat = str_replace('aaaa', 'yyyy', $mapModel->date_format);

        $row = [];
        foreach ($fileArray as $fileKey => $fileValue) {

            foreach ($map as $value) {
                $item = $mapIndex->first(function ($item) use ($value) {
                    return $item->id == $value['mapIndex'];
                });
                if (!$item) {
                    throw new Exception('No existe un indice');
                }
                $row[$item->description] = $fileValue[$value['fileColumn']];
            }
            if ($row['FECHA DE MOVIMIENTO'] == null) {
                continue;
            }
            if (strtotime($row['FECHA DE MOVIMIENTO']) === false) {
                throw new Exception("Fecha de movimiento inválida en {$fileKey}" . json_encode($row), 400);
            }

            $row['CUENTA EXTERNA'] = array_key_exists(strVal($row['NUMERO CUENTA CONTABLE']), $accArray)
                ? $accArray[$row['NUMERO CUENTA CONTABLE']]
                : $row['NUMERO CUENTA CONTABLE'];


            $row['VALOR DEBITO'] = $this->fixedCurrency($separator, $row['VALOR DEBITO']);
            $row['VALOR CREDITO'] = $this->fixedCurrency($separator, $row['VALOR CREDITO']);
            $row['SALDO ACTUAL'] = $this->fixedCurrency($separator, $row['SALDO ACTUAL']);

            $mapped[] = $this->rowToInsert($row, $headerId, $carbonStart, $carbonEnd, $fileKey, $dateFormat);
        }
        return $mapped;
    }

    private function fixedCurrency($separator, $value)
    {
        $value = str_replace('$', '', $value);
        if ($separator == ',') {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        }
        if ($separator == '.') {
            $value = str_replace(',', '', $value);
        }
        return floatval($value);
    }


    private function rowToInsert($row, $headerId, $startDate, $endDate, $fileKey, $dateFormat)
    {
        $carbonCompare = $this->transformDate($dateFormat, $row['FECHA DE MOVIMIENTO']);
        if ($carbonCompare->gte($endDate)) {
            throw new Exception("Fecha de movimiento mayor del rango en {$fileKey}" . json_encode($row), 400);
        }
        if ($carbonCompare->lte($startDate)) {
            throw new Exception("Fecha de movimiento menor de rango en {$fileKey} - " . json_encode($row), 400);
        }
        $row['FECHA DE MOVIMIENTO'] = $carbonCompare->format('Y-m-d');

        return  [
            'header_id' => $headerId,
            'matched' => 0,
            'item_id' => 0,
            'tx_type_id' => null,
            'tx_type_name' => null,
            'cuenta_externa' => array_key_exists('CUENTA EXTERNA', $row) ? $row['CUENTA EXTERNA'] : null,
            'fecha_movimiento' => array_key_exists('FECHA DE MOVIMIENTO', $row) ? $row['FECHA DE MOVIMIENTO'] : null,
            'descripcion' => array_key_exists('DESCRIPCION', $row) ? $row['DESCRIPCION'] : null,
            'referencia_1' => array_key_exists('REFERENCIA 1', $row) ? $row['REFERENCIA 1'] : null,
            'saldo_actual' => array_key_exists('SALDO ACTUAL', $row) ? $row['SALDO ACTUAL'] : null,
            'oficina_destino' => array_key_exists('OFICINA DESTINO', $row) ? $row['OFICINA DESTINO'] : null,
            'nombre_agencia' => array_key_exists('NOMBRE AGENCIA', $row) ? $row['NOMBRE AGENCIA'] : null,
            'nombre_centro_costos' => array_key_exists('NOMBRE CENTRO DE COSTOS', $row) ? $row['NOMBRE CENTRO DE COSTOS'] : null,
            'codigo_centro_costo' => array_key_exists('CODIGO CENTRO DE COSTOS', $row) ? $row['CODIGO CENTRO DE COSTOS'] : null,
            'numero_comprobante' => array_key_exists('NUMERO DE COMPROBANTE', $row) ? $row['NUMERO DE COMPROBANTE'] : null,
            'nombre_usuario' => array_key_exists('NOMBRE DE USUARIO', $row) ? $row['NOMBRE DE USUARIO'] : null,
            'valor_debito_credito' => array_key_exists('VALOR (Debito/Credito)', $row) ? $row['VALOR (Debito/Credito)'] : null,
            'saldo_anterior' => array_key_exists('SALDO ANTERIOR', $row) ? $row['SALDO ANTERIOR'] : null,
            'nombre_cuenta_contable' => array_key_exists('NOMBRE CUENTA CONTABLE', $row) ? $row['NOMBRE CUENTA CONTABLE'] : null,
            'referencia_2' => array_key_exists('REFERENCIA 2', $row) ? $row['REFERENCIA 2'] : null,
            'referencia_3' => array_key_exists('REFERENCIA 3', $row) ? $row['REFERENCIA 3'] : null,
            'nombre_tercero' => array_key_exists('NOMBRE DE TERCERO', $row) ? $row['NOMBRE DE TERCERO'] : null,
            'identificacion_tercero' => array_key_exists('IDENTIFICACION DE TERCERO', $row) ? $row['IDENTIFICACION DE TERCERO'] : null,
            'valor_credito' => array_key_exists('VALOR CREDITO', $row) ? $row['VALOR CREDITO'] : null,
            'valor_debito' => array_key_exists('VALOR DEBITO', $row) ? $row['VALOR DEBITO'] : null,
            'codigo_usuario' => array_key_exists('CODIGO USUARIO', $row) ? $row['CODIGO USUARIO'] : null,
            'fecha_ingreso' => array_key_exists('FECHA INGRESO', $row) ? $row['FECHA INGRESO'] : null,
            'fecha_origen' => array_key_exists('FECHA ORIGEN', $row) ? $row['FECHA ORIGEN'] : null,
            'local_account' => array_key_exists('NUMERO CUENTA CONTABLE', $row) ? $row['NUMERO CUENTA CONTABLE'] : null,
            'numero_lote' => array_key_exists('NUMERO LOTE', $row) ? $row['NUMERO LOTE'] : null,
            'consecutivo_lote' => array_key_exists('CONSECUTIVO LOTE', $row) ? $row['CONSECUTIVO LOTE'] : null,
            'tipo_registro' => array_key_exists('TIPO DE REGISTRO', $row) ? $row['TIPO DE REGISTRO'] : null,
            'ambiente_origen' => array_key_exists('AMBIENTE ORIGEN', $row) ? $row['AMBIENTE ORIGEN'] : null,
            'otra_referencia' => array_key_exists('OTRA REFERENCIA', $row) ? $row['OTRA REFERENCIA'] : null,
            'beneficiario' => array_key_exists('BENEFICIARIO', $row) ? $row['BENEFICIARIO'] : null,
        ];
    }


    private function fileToArray($file)
    {
        // TODO: ACTUALIZAR LIBRERIA PHPSPREADSHEET
        $alphabet = str_split(strtoupper("abcdefghijklmnopqrstuvwxyz"));

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
        $spreadsheet->setActiveSheetIndex(0);
        $worksheet = $spreadsheet->getActiveSheet();
        $highestRow = $worksheet->getHighestDataRow();
        $highestColumn = array_search($worksheet->getHighestColumn(), $alphabet) + 1;
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($worksheet->getHighestColumn());
        $externalInsert = [];


        for ($row = 2; $row <= $highestRow; $row++) {

            $cell = array();
            $insertCell = array();


            for ($col = 1; $col <= $highestColumn; $col++) {

                $typeCell = $worksheet->getCellByColumnAndRow($col, $row)->getDataType();

                switch ($typeCell) {

                    case "null":
                        $valueCell = null;
                        break;
                    case "s":
                        $valueCell = trim($worksheet->getCellByColumnAndRow($col, $row)->getValue());
                        break;
                    case "f":
                        $valueCell = $worksheet->getCellByColumnAndRow($col, $row)->getCalculatedValue();
                        break;

                    case "n":

                        $tmpValueCell = $valueCell = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
                        //validar si es de tipo fecha
                        if (\PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($valueCell = $worksheet->getCellByColumnAndRow($col, $row))) {

                            $tmpValueCell = date("Y-m-d H:i:s", \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($tmpValueCell));
                        }

                        $valueCell = $tmpValueCell;

                        break;

                    default:
                        $tmpValueCell = $valueCell = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
                        break;
                }


                $cell[] = ["value" => $valueCell, "type" => $typeCell];


                $insertCell[] = ($valueCell === null) ? null : $valueCell;
            }
            $localInsert[] = $insertCell;
        }

        return $localInsert;
    }

    public function createTableAccountingItems()
    {
        if (Schema::hasTable($this->accountingItemsTable)) {
            return;
        }
        Schema::create($this->accountingItemsTable, function ($table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('header_id');
            $table->boolean('matched')->default(false);
            $table->integer('item_id')->unsigned();
            $table->integer('tx_type_id')->unsigned()->nullable();
            $table->string('tx_type_name')->nullable();
            $table->dateTime('fecha_movimiento');
            $table->string('descripcion')->comment = 'transaccion/descripcion';
            $table->string('local_account');
            $table->string('cuenta_externa');
            $table->string('referencia_1')->nullable();
            $table->string('referencia_2')->nullable();
            $table->string('referencia_3')->nullable();
            $table->string('otra_referencia')->nullable();
            $table->decimal('saldo_actual', 24, 2)->nullable();
            $table->decimal('valor_debito', 24, 2)->nullable();
            $table->decimal('saldo_anterior', 24, 2)->nullable();
            $table->decimal('valor_credito', 24, 2)->nullable();
            $table->string('codigo_usuario')->nullable();
            $table->string('nombre_agencia')->nullable();
            $table->decimal('valor_debito_credito', 24, 2)->nullable();
            $table->string('nombre_centro_costos')->nullable();
            $table->string('codigo_centro_costo')->nullable();
            $table->string('numero_comprobante')->nullable();
            $table->string('nombre_usuario')->nullable();
            $table->string('nombre_cuenta_contable')->nullable();
            $table->string('numero_cuenta_contable')->nullable();
            $table->string('nombre_tercero')->nullable();
            $table->string('identificacion_tercero')->nullable();
            $table->dateTime('fecha_ingreso')->nullable();
            $table->dateTime('fecha_origen')->nullable();
            $table->string('oficina_origen')->nullable();
            $table->string('oficina_destino')->nullable();
            $table->string('numero_lote')->nullable();
            $table->string('consecutivo_lote')->nullable();
            $table->string('tipo_registro')->nullable();
            $table->string('ambiente_origen')->nullable();
            $table->string('beneficiario')->nullable();

            $table->timestamps();

            $table->foreign('header_id')->references('id')->on('header_accounting_info');

            $table->index(['header_id', 'item_id', 'fecha_movimiento', 'tx_type_id'], 'accountingIndex');
        });
    }

    public function canBeDeleted($id, $startDate, $endDate, $companyId)
    {

        $lastHeader = HeaderAccountingInfo::where('status', HeaderAccountingInfo::STATUS_OPEN)
            ->Orderby('end_date', 'desc')
            ->first();

        if (!$lastHeader) {
            throw new Exception('No existe cargues para eliminar', 400);
        }
        if ($lastHeader->id != $id) {
            throw new Exception('El id no coincide con el último cargue', 400);
        }

        if ($lastHeader->start_date !== $startDate) {
            throw new Exception('La fecha inicial no coincide con el último cargue', 400);
        }

        if ($lastHeader->end_date !== $endDate) {
            throw new Exception('La fecha final no coincide con el último cargue', 400);
        }

        $accountingItemsTable = $this->getAccountinItemsTableName($companyId);

        $items = new AccountingItems($accountingItemsTable);

        DB::beginTransaction();
        try {
            $lastHeader->status = HeaderAccountingInfo::STATUS_DELETED;
            $lastHeader->save();
            $items->where('header_id', $id)->delete();
            HeaderAccountingInfo::where('id', $lastHeader->id)->delete();
            DB::commit();
            return 'Success';
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage(), 500);
        }
    }

    public function getAccountinItemsTableName($companyId)
    {

        return $this->accountingItemsTable = 'accounting_items_' . $companyId;
    }

    public function getHeaderItems($headerId, $companyId)
    {
        $header = HeaderAccountingInfo::where('id', $headerId)
            ->where('company_id', $companyId)
            ->first();

        if ($header) {
            $tableName = $this->getAccountinItemsTableName($companyId);
            $accountingItems = new AccountingItems($tableName);
            return $accountingItems->where('header_id', $header->id)->get();
        }
        throw new Exception('No existen items para el encabezado', 400);
    }
}
