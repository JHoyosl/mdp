<?php

namespace App\Services\ThirdParties;

use Exception;
use Carbon\Carbon;
use App\Models\Account;
use App\Models\MapFile;
use Illuminate\Support\Str;
use App\Models\ExternalTxType;
use App\Models\ThirdPartiesItems;
use Illuminate\Support\Facades\DB;

use App\Models\HeaderThirdPartiesInfo;
use App\Traits\DatesTrait;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;


class ThirdPartiesService
{
    use DatesTrait;

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
        return "hola";
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

        $mapFile = MapFile::find($account->map_id);

        $map =  json_decode($mapFile->map, true);

        $indexMap =  [];
        foreach ($map as $value) {
            $indexMap[$value['fileColumn']] = $value['value'];
        }

        $separator = $mapFile->separator;

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
        $spreadsheet->setActiveSheetIndex(0);
        $worksheet = $spreadsheet->getActiveSheet();

        $startRow = 2;

        $mappedInfo = [];

        foreach ($worksheet->getRowIterator($startRow) as $rowKey => $row) {

            $cellIterator = $row->getCellIterator();
            // $cellIterator->setIterateOnlyExistingCells(true);
            $mappedRow = [];

            // Init  in -1 to start adding before conditions return
            $fileColumn = -1;
            foreach ($cellIterator as  $calumnKey => $cell) {
                $fileColumn++;

                if (\PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($cell)) {
                    $mappedRow[$indexMap[$fileColumn]] = date("Y-m-d H:i:s", \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($cell->getValue()));
                    continue;
                }
                $mappedRow[$indexMap[$fileColumn]] = $cell->getValue();

                if (in_array($indexMap[$fileColumn], ["VALOR CRÉDITO", "VALOR DEBITO", "VALOR (DEBITO/CREDITO)"])) {

                    $value = $mappedRow[$indexMap[$fileColumn]];
                    if ($separator == ',') {
                        $value = $this->currencyToDecimal($value, $separator);
                    }
                    if ($value > 0) {
                        $mappedRow['VALOR CRÉDITO'] =  $value;
                    } else {
                        $mappedRow['VALOR DEBITO'] =  abs($value);
                    }
                }
            }

            if (count($mappedRow) == 0) continue;
            $tmpInsertCell  = $this->cellToInsertExterno($mappedRow, $header->id, $startDate, $endDate);

            $txInfo = $this->getTxInfo($tmpInsertCell, $account->bank_id);

            if ($txInfo[0]) {
                $tmpInsertCell['tx_type_id'] = $txInfo[1]['id'];
                $tmpInsertCell['tx_type_name'] = $txInfo[1]['tx'];
            } else {
                $error = \Illuminate\Validation\ValidationException::withMessages(
                    ['No existe una transacción con descripción: ' . $tmpInsertCell['descripcion'], $tmpInsertCell, $txInfo]
                );
                throw $error;
            }

            $mappedInfo[]  = $tmpInsertCell;
        }

        return $mappedInfo;
    }

    public function cellToInsertExterno($insertCell, $headerId, $startDate, $endDate)
    {
        //Garantee miss match date with time
        $carbonStart = Carbon::parse($startDate)->subDay();
        $carbonEnd = Carbon::parse($endDate)->addDay();

        $moveDate = $insertCell["FECHA DEL MOVIMIENTO"];

        // TODO: Tomar del mapeo el formato, pasar la logica a untrait
        if (strlen($moveDate) == 8) {
            $moveDate = substr_replace(substr_replace($moveDate, '-', 6, 0), '-', 4, 0);
        }
        // $carbonCompare = new Carbon($insertCell["FECHA DEL MOVIMIENTO"]);
        $carbonCompare = Carbon::parse($moveDate);

        if ($carbonCompare->gt($carbonEnd)) {
            throw new Exception("Fecha de movimiento mayor del rango en {$moveDate} - " . json_encode($insertCell), 400);
        }
        if ($carbonCompare->lt($carbonStart)) {
            throw new Exception("Fecha de movimiento menor de rango en {$moveDate} - " . json_encode($insertCell), 400);
        }

        $insert =   [
            'header_id' => $headerId,
            'tx_type_id' => '',
            'tx_type_name' => '',
            'item_id' => '',
            'descripcion' => $insertCell["TIPO DE TRANSACCION/DESCRIPCION"] ?? null,
            'operador' => '', // $insertCell["OPERADOR"],
            'valor_credito' => $insertCell["VALOR CRÉDITO"] ?? null,
            'valor_debito' => $insertCell["VALOR DEBITO"] ?? null,
            'valor_debito_credito' => $insertCell["VALOR (DEBITO/CREDITO)"] ?? null,
            'fecha_movimiento' => $insertCell["FECHA DEL MOVIMIENTO"] ?? null,
            'fecha_archivo' => $insertCell["FECHA DEL ARCHIVO"] ?? null,
            'codigo_tx' => $insertCell["CODIGO DE TRANSACCION"] ?? null,
            'referencia_1' => $insertCell["REFERENCIA 1"] ?? null,
            'referencia_2' => $insertCell["REFERENCIA 2"] ?? null,
            'referencia_3' => $insertCell["REFERENCIA 3"] ?? null,
            'nombre_titular' => $insertCell["NOMBRE TITULAR"] ?? null,
            'identificacion_titular' => $insertCell["IDENTIFICACION TITULAR"] ?? null,
            'numero_cuenta' => $insertCell["NUMERO DE CUENTA"] ?? null,
            'nombre_transaccion' => $insertCell["NOMBRE DE TRANSACCION"] ?? null,
            'consecutivo_registro' => $insertCell["CONSECUTIVO DE REGISTROS"] ?? null,
            'nombre_oficina' => $insertCell["NOMBRE OFICINA"] ?? null,
            'codigo_oficina' => $insertCell["CODIGO OFICINA"] ?? null,
            'canal' => $insertCell["CANAL"] ?? null,
            'nombre_proveedor' => $insertCell["NOMBRE PROVEEDOR"] ?? null,
            'id_proveedor' => $insertCell["IDENTIFICACION DE PROVEEDOR"] ?? null,
            'banco_destino' => $insertCell["BANCO DESTINO"] ?? null,
            'fecha_rechazo' => $insertCell["FECHA DE RECHAZO"] ?? null,
            'motivo_rechazo' => $insertCell["MOTIVO DE RECHAZO"] ?? null,
            'ciudad' => $insertCell["CIUDAD"] ?? null,
            'tipo_cuenta' => $insertCell["TIPO DE CUENTA"] ?? null,
            'numero_documento' => $insertCell["NUMERO DE DOCUMENTO"] ?? null,

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

    //TODO: Improve this implementation
    public function currencyToDecimal($value, $separator)
    {
        $value = str_replace("$", "", $value);
        if ($separator == ".") {
            $value = str_replace(",", "", $value);
            $value = str_replace(".", ".", $value);
            return  floatval($value);
        }
        $value = str_replace(".", "", $value);
        $value = str_replace(",", ".", $value);

        return  floatval($value);
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
