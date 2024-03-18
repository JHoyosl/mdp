<?php

namespace App\Services\Reconciliation;

use Exception;
use App\Models\User;
use App\Models\Account;
use App\Models\ExternalTxType;
use App\Models\ReconciliationItem;
use App\Models\ReconciliationHeader;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ReconciliationService
{


    public function IniReconciliation($date, $file, $user, $companyId)
    {
        $tableName = $this->headerTableName($companyId);
        if (!Schema::hasTable($tableName)) {
            $this->createReconciliationHeadersTable($tableName);
        }

        $headerTable = new ReconciliationHeader($tableName);

        // if ($headerTable->first()) {
        //     throw new Exception('Ya existe una inicial', 400);
        // }

        $filePath = $this->saveIniReconciliationFile($file, $companyId);

        // $headerTable->insert(
        //     [
        //         'fecha_ini' => date('Y-m-d H:i:s'),
        //         'fecha_end' => date('Y-m-d H:i:s'),
        //         'created_by' => $user->id,
        //         'step' => 1,
        //         'status' => ReconciliationHeader::OPEN_STATUS,
        //         'type' => ReconciliationHeader::TYPE_INITIAL
        //     ]
        // );

        $initialHeader = $headerTable->first();

        $externalInfo = $this->getIniExternalArray($user, $filePath);

        return $this->iniExternalInfoToInsert($externalInfo);

        return $headerTable->first();

        // return $headers->first();

        return [$date, $user, $file];
    }

    public function iniExternalInfoToInsert($externalInfo)
    {
        $externalInsert = [];

        $accountNumber = $externalInfo[0][2];

        $account = Account::where('bank_account', $accountNumber)
            ->with('banks')
            ->first();

        if (!$account) {
            throw new Exception(`No existe la cuenta {$accountNumber}`);
        }

        foreach ($externalInfo as $key => $row) {
            if ($row[2] != $accountNumber) {
                $accountNumber = $row[2];
                $account = Account::where('bank_account', $accountNumber)
                    ->with('banks')
                    ->first();

                if (!$account) {
                    throw new Exception(`No existe la cuenta {$accountNumber}`);
                }
            }

            $txTypeId = null;
            $txTypeName = null;

            $txType = ExternalTxType::where('bank_id', $account->bank_id)
                ->where('description', trim($row[7]))
                ->first();

            if ($txType) {
                $txTypeId = $txType->id;
                $txTypeName = $txType->tx;
            }

            $txType = ExternalTxType::where('type', 'COMPUESTA')
                ->orWhere('type', 'COMPUEST0')
                ->get();


            for ($i = 0; $i < count($txType); $i++) {

                if (strpos(strtoupper($row[7]), $txType[$i]->description) !== false) {

                    $txTypeId = $txType[$i]->id;
                    $txTypeName = $txType[$i]->tx;
                    break;
                }
            }

            if (!$txTypeId) {
                throw new Exception('No existe una transacción con descripción: ' . $row[7], 400);
            }

            $externalInsert[] = [
                'tx_type_id' => $txTypeId,
                'tx_type_name' => $txTypeName,
                'numero_cuenta' => $row[2],
                'fecha_movimiento' => date('Y-m-d', strtotime($row[3])),
                'referencia_1' => $row[4],
                'referencia_2' => $row[5],
                'referencia_3' => $row[6],
                'descripcion' => $row[7],
                'valor_credito' => $row[8],
                'valor_debito' => $row[9],
            ];
        }
        return $externalInsert;
    }

    public function getIniExternalArray($user, $filePath)
    {

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
        $spreadsheet->setActiveSheetIndex(0);
        $worksheet = $spreadsheet->getActiveSheet();

        $startRow = 3;

        $data = [];
        foreach ($worksheet->getRowIterator($startRow) as $rowKey => $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(true);

            $rows = [];
            foreach ($cellIterator as  $columnKey => $cell) {

                if (\PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($cell)) {
                    $rows[] = date("Y-m-d H:i:s", \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($cell->getValue()));
                    continue;
                }
                $rows[] = $cell->getValue();
                // TODO: REMOVER
                if ($columnKey == 'J') {
                    break;
                }
                // TODO: REMOVER
            }
            $data[] = $rows;

            // TODO: REMOVER
            if ($rowKey == '36') {
                break;
            }
            // TODO: REMOVER
        }

        return $data;
    }

    public function balanceCloseAccount($externalBalance, $localBalance, $accountId, $companyId)
    {
        $conciliarItemsTable = 'conciliar_items_' . $companyId;
        $conciliarHeaderTable = 'conciliar_headers_' . $companyId;

        $itemTable = new ReconciliationItem($conciliarItemsTable);
        $headers = new ReconciliationItem($conciliarHeaderTable);

        $openHeader = $headers->where('status', ReconciliationItem::OPEN_STATUS)
            ->orderBy('id', 'desc')->first();


        $openItemTable = $itemTable->where('header_id', '=', $openHeader->id)
            ->where('account_id', '=', $accountId)
            ->first();

        $lastBalance = $this->getLastAccountBalance($companyId, $accountId);

        // return $this->localDifference();
        return [$externalBalance, $localBalance, $accountId, $companyId];
    }

    public function localDifference(ReconciliationItem $itemsTable, $lastLocalBalance, $localBalance)
    {
        $calcValue = $lastLocalBalance + $itemsTable->debit_local - $itemsTable->credit_local;
        $diference = $calcValue - $localBalance;
        return $diference;
    }

    public function getLastAccountBalance($companyId, $accountId)
    {

        $conciliarHeaderTableName = 'conciliar_headers_' . $companyId;
        $conciliarItemsTableName = 'conciliar_items_' . $companyId;
        $conciliarHeaderTable = new ReconciliationItem($conciliarHeaderTableName);

        $conciliarHeaderClose = $conciliarHeaderTable->where('status', '=', ReconciliationItem::CLOSE_STATUS)
            ->orderBy('id', 'desc')
            ->first();

        $conciliarItemsTable = new ReconciliationItem($conciliarItemsTableName);

        $conciliarItemsClose = $conciliarItemsTable
            ->where('header_id', '=', $conciliarHeaderClose->id)
            ->where('account_id', '=', $accountId)
            ->first();

        return [
            "balanceExterno" => $conciliarItemsClose->balance_externo,
            "balanceLocal" => $conciliarItemsClose->balance_local
        ];
    }

    // HELPERS
    public function headerTableName($companyId): string
    {
        return 'reconciliation_headers_' . $companyId;
    }

    public function saveIniReconciliationFile($file, $companyId)
    {

        $ext = $file->extension() == 'txt' ? 'csv' : $file->extension();
        $filePath = $companyId . '/reconciliation/initial.' . $ext;
        Storage::disk('reconciliation')->put($filePath, file_get_contents($file));

        return storage_path('reconciliation') . '/' . $filePath;
    }

    // TABLE CREATION
    public function createTmpTableConciliarItems(String $companyId)
    {
        $tmpItems = 'conciliar_tmp_items_' . $companyId;

        Schema::create($tmpItems, function ($table) {
            $table->increments('id');
            $table->integer('header_id')->unsigned();
            $table->integer('account_id')->unsigned();
            $table->decimal('debit_externo', 24, 2);
            $table->decimal('credit_externo', 24, 2);
            $table->decimal('debit_local', 24, 2);
            $table->decimal('credit_local', 24, 2);
            $table->decimal('balance_externo', 24, 2);
            $table->decimal('balance_local', 24, 2);
            $table->decimal('total', 24, 2);
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('status')->default('created');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('accounts');
        });
    }

    public function createTmpTableConciliarExternalValues(String $companyId)
    {
        // TODO: Recibir el nombre de la table en lugar del comnpanyId
        $tmpExternalValues = 'conciliar_tmp_external_values_' . $companyId;
        Schema::dropIfExists($this->$tmpExternalValues);

        Schema::create($tmpExternalValues, function ($table) {
            $table->bigIncrements('id');
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
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function createReconciliationHeadersTable($tableName)
    {
        if (Schema::hasTable($tableName)) {
            throw new Exception(`Ya existe la table {$tableName}`, 400);
        };

        Schema::create($tableName, function ($table) {
            $table->increments('id');
            $table->integer('created_by')->unsigned();
            $table->integer('close_by')->unsigned()->nullable();
            $table->date('fecha_ini');
            $table->date('fecha_end');
            $table->string('step');
            $table->string('type');
            $table->string('status')->default('created');
            $table->softDeletes();
            $table->timestamps();

            $table->index(['status', 'fecha_ini', 'fecha_end', 'type']);

            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('close_by')->references('id')->on('users');
        });
    }
}
