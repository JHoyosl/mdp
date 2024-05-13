<?php

namespace App\Services\ThirdParties;

use Exception;
use Carbon\Carbon;
use App\Models\Account;
use App\Models\MapFile;
use App\Traits\DatesTrait;
use Illuminate\Support\Str;
use App\Models\ExternalTxType;
use App\Models\ThirdPartiesItems;

use Illuminate\Support\Facades\DB;
use App\Models\HeaderThirdPartiesInfo;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use App\Services\MappingFile\MappingFileService;


class ThirdPartiesService
{
    use DatesTrait;

    private MappingFileService $mappingFileService;

    public function __construct(MappingFileService $mappingFileService)
    {
        $this->mappingFileService = $mappingFileService;
    }

    public function getAccInfoToReconciliate($accountId, $startDate, $endDate)
    {
        $tableName = $this->getThirdPartiesItemsTableName($accountId);
        $itemsTable = new ThirdPartiesItems($tableName);

        $items  = $itemsTable
            ->whereBetween('fecha_movimiento', [$startDate, $endDate])
            ->get();

        return $items;
    }

    public function getThirdPartyHeadersByDate($companyId,  $accountId, $date)
    {
        $headerInfo = HeaderThirdPartiesInfo::where('company_id', $companyId)
            ->where('account_id', $accountId)
            ->where('start_date', '<=', $date)
            ->orderBy('end_date', 'ASC')
            ->get();
        return $headerInfo;
    }

    public function getLastHeaderByAccount($accountId, $companyId)
    {
        $headerInfo = HeaderThirdPartiesInfo::where('company_id', $companyId)
            ->where('account_id', $accountId)
            ->orderBy('end_date', 'ASC')
            ->first();
        return $headerInfo;
    }

    public function getAccountHeaderInfo($companyId, $accountId)
    {
        $headerInfo = HeaderThirdPartiesInfo::where('company_id', $companyId)
            ->where('account_id', $accountId)
            ->get();

        return $headerInfo;
    }

    public function getThirdPartiesAccounts($companyId)
    {
        $accounts = Account::where('company_id', $companyId)
            ->with('banks')
            ->orderBy('bank_id')
            ->get();
        return $accounts;
    }

    public function getHeaderItems($headerId)
    {
        $header = HeaderThirdPartiesInfo::where('id', $headerId)->first();

        if (!$header) {
            throw new Exception('No se encuentra el encabezado', 400);
        }

        $tableName = $this->getThirdPartiesItemsTableName($header->account_id);
        $itemsTable = new ThirdPartiesItems($tableName);

        $items = $itemsTable->where('header_id', $header->id)->get();

        return $items;
    }

    public function deletelastHeaderInfo($headerId, $accountId, $startDate, $endDate, $companyId)
    {

        $lastHeader = HeaderThirdPartiesInfo::where('account_id', $accountId)
            ->where('status', HeaderThirdPartiesInfo::STATUS_OPEN)
            ->Orderby('end_date', 'desc')
            ->first();

        if (!$lastHeader) {
            throw new Exception('No existe cargues para eliminar', 400);
        }
        if ($lastHeader->id != $headerId) {
            throw new Exception('El id no coincide con el último cargue', 400);
        }

        if ($lastHeader->start_date !== $startDate) {
            throw new Exception('La fecha inicial no coincide con el último cargue', 400);
        }

        if ($lastHeader->end_date !== $endDate) {
            throw new Exception('La fecha final no coincide con el último cargue', 400);
        }

        $tableName = $this->getThirdPartiesItemsTableName($lastHeader->account_id);

        $items = new ThirdPartiesItems($tableName);

        DB::beginTransaction();
        try {
            $lastHeader->status = HeaderThirdPartiesInfo::STATUS_DELETED;
            $lastHeader->save();
            $items->where('header_id', $headerId)->delete();
            HeaderThirdPartiesInfo::where('id', $lastHeader->id)->delete();

            DB::commit();
            return 'Success';
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage(), 500);
        }
    }

    public function uploadAccountInfo($user, $accountId, $companyId, $file, $startDate, $endDate)
    {
        // ini_set('memory_limit', '-1');

        $this->dateValidation($accountId, $startDate);

        DB::beginTransaction();

        try {
            $newHeader = $this->getAccountItemInfo(
                $user,
                $accountId,
                $companyId,
                $startDate,
                $endDate,
                $file
            );

            $this->getInsertData($accountId, $newHeader, $file, $startDate, $endDate);

            DB::commit();

            return $newHeader;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage(), 500);
        }
    }


    // Check for consecutive date
    public function  dateValidation($accountId, $startDate)
    {
        $lastHeader = HeaderThirdPartiesInfo::where('account_id', $accountId)
            ->orderBy('created_at', 'DESC')
            ->first();

        if ($lastHeader) {
            $headerDate = Carbon::parse($lastHeader->end_date);
            $nextDay = $headerDate->addDay(1);

            if ($nextDay->ne($startDate)) {
                throw new Exception("El cargue debe comenzar en {$nextDay->format('Y-m-d')} y el actual es {$startDate}");
            }
        }
    }

    public function getAccountItemInfo($user, $accountId, $companyId, $startDate, $endDate, $file)
    {
        $path = $companyId . '/' . $accountId;

        if (!is_dir(storage_path($path))) {
            mkdir(storage_path($path), 0775, true);
        }

        $storedPath = Storage::disk('third-parties')->put($path, $file);

        $headerInfo = [
            'account_id' => $accountId,
            'company_id' => $companyId,
            'uploaded_by' => $user->id,
            'path' => $storedPath,
            'file_name' => $file->getClientOriginalName(),
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];

        $newHeader = HeaderThirdPartiesInfo::create($headerInfo);

        return $newHeader;
    }



    public function getInsertData($accountId, $header, $file, $startDate, $endDate)
    {
        $account = Account::with('map')->find($accountId);

        //Create table if not exist
        if (!Schema::hasTable($this->getThirdPartiesItemsTableName($accountId))) {
            $this->createThirdPartiesItems($accountId);
        }

        // Get mapping  model
        $mappedInfo = $this->mapInfo($account, $header, $file, $startDate, $endDate);

        $header->rows = count($mappedInfo);
        $header->save();
        $this->insertInfo($mappedInfo, $account->id, $startDate, $endDate);


        return $account;
    }

    public function insertInfo($mappedInfo, $accountId)
    {
        $tableName = $this->getThirdPartiesItemsTableName($accountId);

        foreach (array_chunk($mappedInfo, 500) as $t) {
            DB::table($tableName)->insert($t);
        }
    }

    public function mapInfo($account, $header, $file, $startDate, $endDate)
    {
        $mapIndex = $this->mappingFileService->getMapIndex(MapFile::TYPE_EXTERNAL);
        $mapFile = MapFile::find($account->map_id);
        $map = json_decode($mapFile->map, true);
        $separator = $mapFile->separator;
        $dateFormat = str_replace('aaaa', 'Y', $mapFile->date_format);
        $dateFormat = str_replace('mm', 'm', $dateFormat);
        $dateFormat = str_replace('dd', 'd', $dateFormat);

        $fileArray = $this->fileToArray($file, $mapFile->skip_top);
        $carbonStart = Carbon::parse($startDate)->subDay();
        $carbonEnd = Carbon::parse($endDate)->addDay();

        $mappedInfo = [];

        foreach ($fileArray as $fileKey => $fileValue) {
            $row = [];
            foreach ($map as $value) {
                if ($value['mapIndex'] == "null") {
                    continue;
                }
                $item = $mapIndex->first(function ($item) use ($value) {
                    return $item->id == $value['mapIndex'];
                });
                if (!$item) {
                    return [$value, $mapIndex, $item];
                    throw new Exception('No existe un indice');
                }
                $row[$item->description] = $fileValue[$value['fileColumn']];
            }

            // Validate dates
            $row['FECHA DEL MOVIMIENTO'] = Carbon::parse($row['FECHA DEL MOVIMIENTO']);
            if ($row['FECHA DEL MOVIMIENTO']->gt($carbonEnd)) {
                throw new Exception("Fecha de movimiento mayor del rango en {$row['FECHA DEL MOVIMIENTO']->format('Y/m/d')} - " . json_encode($row), 400);
            }
            if ($row['FECHA DEL MOVIMIENTO']->lt($carbonStart)) {
                throw new Exception("Fecha de movimiento menor de rango en {$row['FECHA DEL MOVIMIENTO']->format('Y/m/d')} - " . json_encode($row), 400);
            }
            $row['FECHA DEL MOVIMIENTO'] = $row['FECHA DEL MOVIMIENTO']->format('Y/m/d');

            // fix currency and decimal separtor
            $row['VALOR DEBITO'] = $this->fixedCurrency($separator, $row['VALOR DEBITO']);
            $row['VALOR CRÉDITO'] = $this->fixedCurrency($separator, $row['VALOR CRÉDITO']);

            if (array_key_exists('VALOR (DEBITO/CREDITO)', $row)) {
                $row['VALOR (DEBITO/CREDITO'] = $this->fixedCurrency($separator, $row['VALOR (DEBITO/CREDITO']);
                if ($row['VALOR (DEBITO/CREDITO'] > 0) {
                    $mappedRow['VALOR CRÉDITO'] =  $row['VALOR (DEBITO/CREDITO'];
                } else {
                    $mappedRow['VALOR DEBITO'] =  abs($row['VALOR (DEBITO/CREDITO']);
                }
            }
            $mappedInfo[] = $this->cellToInsertExterno($row,  $header->id);
        }

        return $mappedInfo;
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

    private function fileToArray($file, $skipTop)
    {
        $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($file);
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
        $spreadsheet = $reader->load($file);

        // $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
        $spreadsheet->setActiveSheetIndex(0);
        $worksheet = $spreadsheet->getActiveSheet();
        $highestRow = $worksheet->getHighestDataRow();
        $reader->setReadDataOnly(FALSE);
        $rows = [];
        foreach ($worksheet->getRowIterator() as $keyRow => $row) {
            if ($skipTop >= $keyRow - 1) {
                continue;
            }
            if ($keyRow > $highestRow) {
                break;
            }
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(FALSE); // This loops through all cells,
            $cells = [];
            foreach ($cellIterator as $keyCell => $cell) {
                $value = $worksheet->getCell($keyCell . $keyRow);
                if (\PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($worksheet->getCell($keyCell . $keyRow))) {
                    $cells[] = date("Y-m-d", \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($value->getValue()));
                    continue;
                }
                $cells[] = $worksheet->getCell($keyCell . $keyRow)->getValue();
            }

            $rows[] = $cells;
        }

        return $rows;
    }

    public function cellToInsertExterno($row, $headerId)
    {
        $insert =   [
            'header_id' => $headerId,
            'tx_type_id' => '',
            'tx_type_name' => '',
            'item_id' => '',
            'descripcion' => $row["TIPO DE TRANSACCION/DESCRIPCION"] ?? null,
            'operador' => '', // $insertCell["OPERADOR"],
            'valor_credito' => $row["VALOR CRÉDITO"] ?? null,
            'valor_debito' => $row["VALOR DEBITO"] ?? null,
            'valor_debito_credito' => $row["VALOR (DEBITO/CREDITO)"] ?? null,
            'fecha_movimiento' => $row["FECHA DEL MOVIMIENTO"] ?? null,
            'fecha_archivo' => $row["FECHA DEL ARCHIVO"] ?? null,
            'codigo_tx' => $row["CODIGO DE TRANSACCION"] ?? null,
            'referencia_1' => $row["REFERENCIA 1"] ?? null,
            'referencia_2' => $row["REFERENCIA 2"] ?? null,
            'referencia_3' => $row["REFERENCIA 3"] ?? null,
            'nombre_titular' => $row["NOMBRE TITULAR"] ?? null,
            'identificacion_titular' => $row["IDENTIFICACION TITULAR"] ?? null,
            'numero_cuenta' => $row["NUMERO DE CUENTA"] ?? null,
            'nombre_transaccion' => $row["NOMBRE DE TRANSACCION"] ?? null,
            'consecutivo_registro' => $row["CONSECUTIVO DE REGISTROS"] ?? null,
            'nombre_oficina' => $row["NOMBRE OFICINA"] ?? null,
            'codigo_oficina' => $row["CODIGO OFICINA"] ?? null,
            'canal' => $row["CANAL"] ?? null,
            'nombre_proveedor' => $row["NOMBRE PROVEEDOR"] ?? null,
            'id_proveedor' => $row["IDENTIFICACION DE PROVEEDOR"] ?? null,
            'banco_destino' => $row["BANCO DESTINO"] ?? null,
            'fecha_rechazo' => $row["FECHA DE RECHAZO"] ?? null,
            'motivo_rechazo' => $row["MOTIVO DE RECHAZO"] ?? null,
            'ciudad' => $row["CIUDAD"] ?? null,
            'tipo_cuenta' => $row["TIPO DE CUENTA"] ?? null,
            'numero_documento' => $row["NUMERO DE DOCUMENTO"] ?? null,

        ];

        return $insert;
    }

    public function getTxInfo($values, $bank_id)
    {
        $externalTxTable = ExternalTxType::where('bank_id', $bank_id)
            ->where('reference', 'like', '%' . $values['codigo_tx'] . '%')
            ->get();

        if (is_numeric($values['codigo_tx'])) {

            for ($j = 0; $j < count($externalTxTable); $j++) {

                if (intval($externalTxTable[$j]['reference']) == intval($values['codigo_tx'])) {

                    return [true, $externalTxTable[0]];
                } else {

                    if ($externalTxTable[$j]['reference'] == $values['codigo_tx']) {

                        return [true, $externalTxTable[0]];
                    }
                }
            }
        }

        $externalTxTable = ExternalTxType::where('bank_id', $bank_id)
            ->where('reference', 'like', '%' . $values['codigo_tx'] . '%')
            ->get();

        if (count($externalTxTable) > 0) {

            return [true, $externalTxTable[0]];
        }

        $externalTxTable = ExternalTxType::where('bank_id', $bank_id)
            ->where('description', 'like', '%' . $values['descripcion'] . '%')
            ->get();

        if (count($externalTxTable) > 0) {

            return [true, $externalTxTable[0]];
        } else {

            return [false, $externalTxTable];
        }
    }

    public function getThirdPartiesItemsTableName(String $accountId)
    {
        return 'third_parties_items_' . $accountId;
    }

    //Table creation
    public function createThirdPartiesItems(String $accountId)
    {
        $tableName = $this->getThirdPartiesItemsTableName($accountId);

        Schema::create($tableName, function ($table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('header_id');
            $table->boolean('matched')->default(false);
            $table->integer('tx_type_id')->unsigned();
            $table->string('tx_type_name')->nullable();
            $table->integer('item_id')->unsigned()->nullable();
            $table->string('descripcion')->comment = 'transaccion/descripcion';
            $table->string('operador')->nullable();
            $table->decimal('valor_credito', 24, 2)->nullable();
            $table->decimal('valor_debito', 24, 2)->nullable();
            $table->decimal('valor_debito_credito', 24, 2)->nullable();
            $table->dateTime('fecha_movimiento')->nullable();
            $table->dateTime('fecha_archivo')->nullable();
            $table->string('codigo_tx')->nullable();
            $table->string('referencia_1')->nullable();
            $table->string('referencia_2')->nullable();
            $table->string('referencia_3')->nullable();
            $table->string('nombre_titular')->nullable();
            $table->string('identificacion_titular')->nullable();
            $table->string('numero_cuenta')->nullable();
            $table->string('nombre_transaccion')->nullable();
            $table->string('consecutivo_registro')->nullable();
            $table->string('nombre_oficina')->nullable();
            $table->string('codigo_oficina')->nullable();
            $table->string('canal')->nullable();
            $table->string('nombre_proveedor')->nullable();
            $table->string('id_proveedor')->nullable();
            $table->string('banco_destino')->nullable();
            $table->dateTime('fecha_rechazo')->nullable();
            $table->string('motivo_rechazo')->nullable();
            $table->string('ciudad')->nullable();
            $table->string('tipo_cuenta')->nullable();
            $table->string('numero_documento')->nullable();

            $table->timestamps();
        });
    }
}
