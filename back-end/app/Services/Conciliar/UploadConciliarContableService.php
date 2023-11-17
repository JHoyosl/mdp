<?php

namespace App\Services\Conciliar;

use Exception;
use App\Models\MapFile;
use App\Models\ConciliarItem;
use App\Models\ConciliarHeader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UploadConciliarContableService
{
    protected $conciliar_tmp_local_values_table = '';
    protected $conciliar_items_table = '';

    function __construct()
    {
    }

    public function startUploadProcess($file, $map_id, $conciliarLocalValues, $conciliarItemsTable, $user, $openHeader)
    {


        $this->conciliar_tmp_local_values_table = $conciliarLocalValues;
        $this->conciliar_items_table = $conciliarItemsTable;

        $mapped = $this->getInsertConciliarLocal($file, $map_id);

        $this->createTmpTableConciliarLocalValues();

        DB::beginTransaction();

        try {

            foreach (array_chunk($mapped, 1000) as $t) {
                DB::table($this->conciliar_tmp_local_values_table)->insert($t);
            }
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e);
        }
        DB::commit();


        $conciliarCuadre = DB::table($this->conciliar_tmp_local_values_table)
            ->select(
                DB::raw("SUM(valor_credito) as credit,SUM(valor_debito) as debit, 
                            " . $this->conciliar_tmp_local_values_table . ".local_account, accounts.id")
            )
            ->join('accounts', $this->conciliar_tmp_local_values_table . '.local_account', '=', 'accounts.local_account')
            ->join('banks', 'accounts.bank_id', '=', 'banks.id')
            ->where('accounts.company_id', '=', $user->current_company)
            ->groupBy('local_account', 'accounts.id')
            ->get();


        // Schema::dropIfExists($this->conciliar_tmp_local_values_table);
        // return $conciliarCuadre;
        $itemTable = new ConciliarItem($this->conciliar_items_table);

        for ($i = 0; $i < count($conciliarCuadre); $i++) {

            $openItemTable = $itemTable->where('header_id', '=', $openHeader->id)
                ->where('account_id', '=', $conciliarCuadre[$i]->id)
                ->first();

            if ($openItemTable) {

                $openItemTable->debit_local = (float)$conciliarCuadre[$i]->debit;
                $openItemTable->credit_local = (float)$conciliarCuadre[$i]->credit;

                $openItemTable->save();
            } else {

                $itemInfo = [
                    'header_id' => $openHeader->id,
                    'account_id' => $conciliarCuadre[$i]->id,
                    'debit_externo' => 0,
                    'debit_local' => $conciliarCuadre[$i]->debit,
                    'credit_externo' => 0,
                    'credit_local' => $conciliarCuadre[$i]->credit,
                    'balance_externo' => 0,
                    'balance_local' => 0,
                    'file_path' => '',
                    'file_name' => '',
                    'total' => 0,
                    'status' => ConciliarHeader::OPEN_STATUS,
                ];

                $itemTable->insert($itemInfo);
            }
        }

        return $conciliarCuadre;
    }

    public function getInsertConciliarLocal($file, $map_id)
    {

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
            $mapped[] =  [
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

            if (strtotime($fileArray[$i][$tmpArray[1]]) === false) {
                throw new Exception("Fecha de movimiento inválida en {$i} - {$fileArray[$i][$tmpArray[1]]}" . json_encode($mapped[$i]));
            }
        }
        return $mapped;
    }

    private function fileToArray($file)
    {
        //TODO: ACTUALIZAR LIBRERIA PHPSPREADSHEET
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

    public function createTmpTableConciliarLocalValues()
    {

        Schema::dropIfExists($this->conciliar_tmp_local_values_table);

        Schema::create($this->conciliar_tmp_local_values_table, function ($table) {
            $table->bigIncrements('id');
            $table->boolean('matched')->default(false);
            $table->integer('item_id')->unsigned()->nullable();
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

            $table->softDeletes();
            $table->timestamps();
        });
    }
}
