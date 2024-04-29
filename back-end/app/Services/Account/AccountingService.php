<?php

namespace App\Services\Account;

use Exception;
use App\Models\Account;
use App\Models\Company;
use App\Models\MapFile;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Models\AccountingItems;
use Illuminate\Support\Facades\DB;
use App\Models\HeaderAccountingInfo;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class AccountingService
{

    protected $accountingItemsTable = '';

    public function __construct()
    {
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

        return HeaderAccountingInfo::all();
    }

    public function uploadAccountInfo($user, $file, $startDate, $endDate, $company)
    {
        ini_set('memory_limit', '-1');
        $this->accountingItemsTable = $this->getAccountinItemsTableName($user->current_company);

        $this->createTableAccountingItems();

        DB::beginTransaction();

        try {

            $newHeader = $this->createAccountingHeaderInfo(
                $user->current_company,
                $user->id,
                $file,
                $startDate,
                $endDate
            );

            $mapped = $this->getInsertConciliarLocal($file, $company->map_id, $startDate, $endDate, $newHeader->id);
            return $mapped;
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

    public function getInsertConciliarLocal($file, $map_id, $startDate, $endDate, $headerId)
    {
        //Garantee miss match date with time
        $carbonStart = Carbon::parse($startDate)->subDay();
        $carbonEnd = Carbon::parse($endDate)->addDay();
        $fileArray = $this->fileToArray($file);

        $mapModel = MapFile::find($map_id);

        $map = json_decode($mapModel->map, true);


        $tmpArray = array();
        $tmpArray[] = null;

        for ($i = 1; $i <= 31; $i++) {
            $found = false;
            for ($j = 0; $j < count($map); $j++) {

                if ($i == $map[$j]['mapIndex']) {
                    $found = true;
                    $tmpArray[] = (string)$map[$j]['fileColumn'];
                }
            }
            if (!$found) {

                $tmpArray[] = null;
            }
        }

        for ($i = 0; $i < count($fileArray); $i++) {
            // return [$fileArray[$i][5], $tmpArray[1]];
            if ($fileArray[$i][$tmpArray[1]] == 0) {
                continue;
            }
            if ($fileArray[$i][$tmpArray[20]] == null) {
                continue;
            }
            // Check if date is valid
            if (strtotime($fileArray[$i][$tmpArray[1]]) === false) {
                throw new Exception("Fecha de movimiento inválida en {$i} - {$fileArray[$i][$tmpArray[1]]}" . json_encode($fileArray[$i][$tmpArray[1]]), 400);
            }

            // Check if date is in range
            $carbonCompare = Carbon::createFromFormat('Ymd', $fileArray[$i][$tmpArray[1]]);
            if ($carbonCompare->gte($carbonEnd)) {
                throw new Exception("Fecha de movimiento mayor del rango en {$i} - " . json_encode($fileArray[$i][$tmpArray[1]]), 400);
            }
            if ($carbonCompare->lte($carbonStart)) {
                throw new Exception("Fecha de movimiento menor de rango en {$i} - " . json_encode($fileArray[$i][$tmpArray[1]]), 400);
            }

            $mapped[] =  [
                'header_id' => $headerId,     //1
                'matched' => 0,     //1
                'item_id' => 0,     //2
                'tx_type_id' => null,       //3
                'tx_type_name' => null,     //4
                'cuenta_externa' => $tmpArray[3] == null ? '' : $fileArray[$i][$tmpArray[3]],
                'fecha_movimiento' => $tmpArray[1] == null ? null : $fileArray[$i][$tmpArray[1]],
                'descripcion' => $tmpArray[2] == null ? null : $fileArray[$i][$tmpArray[2]],
                'referencia_1' => $tmpArray[4] == null ? null : $fileArray[$i][$tmpArray[4]],
                'saldo_actual' => $tmpArray[8] == null ? null : $fileArray[$i][$tmpArray[8]],
                'oficina_destino' => $tmpArray[26] == null ? null : $fileArray[$i][$tmpArray[26]],
                'nombre_agencia' => $tmpArray[13] == null ? null : $fileArray[$i][$tmpArray[13]],
                'nombre_centro_costos' => $tmpArray[15] == null ? null : $fileArray[$i][$tmpArray[15]],
                'codigo_centro_costo' => $tmpArray[16] == null ? null : $fileArray[$i][$tmpArray[16]],
                'numero_comprobante' => $tmpArray[17] == null ? null : $fileArray[$i][$tmpArray[17]],
                'nombre_usuario' => $tmpArray[18] == null ? null : $fileArray[$i][$tmpArray[18]],
                'valor_debito_credito' => $tmpArray[14] == null ? null : $fileArray[$i][$tmpArray[14]],
                'saldo_anterior' => $tmpArray[10] == null ? null : $fileArray[$i][$tmpArray[10]],
                'nombre_cuenta_contable' => $tmpArray[19] == null ? null : $fileArray[$i][$tmpArray[19]],
                'referencia_2' => $tmpArray[5] == null ? null : $fileArray[$i][$tmpArray[5]],
                'referencia_3' => $tmpArray[6] == null ? null : $fileArray[$i][$tmpArray[6]],
                'nombre_tercero' => $tmpArray[16] == null ? null : $fileArray[$i][$tmpArray[16]],
                'identificacion_tercero' => $tmpArray[21] == null ? null : $fileArray[$i][$tmpArray[21]],
                'valor_credito' => $tmpArray[11] == null ? null : $fileArray[$i][$tmpArray[11]],
                'valor_debito' => $tmpArray[9] == null ? null : $fileArray[$i][$tmpArray[9]],
                'codigo_usuario' => $tmpArray[12] == null ? null : $fileArray[$i][$tmpArray[12]],
                'fecha_ingreso' => $tmpArray[23] == null ? null : $fileArray[$i][$tmpArray[23]],
                'fecha_origen' => $tmpArray[24] == null ? null : $fileArray[$i][$tmpArray[24]],
                'local_account' => $tmpArray[20] == null ? null : $fileArray[$i][$tmpArray[20]],
                'numero_lote' => $tmpArray[27] == null ? null : $fileArray[$i][$tmpArray[27]],
                'consecutivo_lote' => $tmpArray[28] == null ? null : $fileArray[$i][$tmpArray[28]],
                'tipo_registro' => $tmpArray[29] == null ? null : $fileArray[$i][$tmpArray[29]],
                'ambiente_origen' => $tmpArray[30] == null ? null : $fileArray[$i][$tmpArray[30]],
                'otra_referencia' => $tmpArray[7] == null ? null : $fileArray[$i][$tmpArray[7]],
                'beneficiario' => $tmpArray[31] == null ? null : $fileArray[$i][$tmpArray[31]],
            ];
        }
        return $mapped;
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
