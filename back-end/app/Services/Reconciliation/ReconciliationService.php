<?php

namespace App\Services\Reconciliation;

use Exception;
use Carbon\Carbon;
use App\Models\Account;
use App\Models\LocalTxType;
use Illuminate\Support\Str;
use App\Models\ExternalTxType;
use App\Models\ReconciliationItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use App\Models\ReconciliationLocalValues;
use Illuminate\Database\Schema\Blueprint;
use App\Models\ReconciliationExternalValues;

class ReconciliationService
{

    public function getAccountProcess($companyId)
    {
        $ItemstableName = $this->getReconciliationItemTableName($companyId);
        if (!Schema::hasTable($ItemstableName)) {
            return [];
        }
        $items = Account::join($ItemstableName, 'accounts.id', $ItemstableName . '.id')
            ->join('banks', 'banks.id', 'accounts.bank_id')
            ->where('company_id', $companyId)
            ->orderBy('start_date', 'DESC')
            ->orderBy('account_id', 'DESC')
            ->get();
        return $items;
    }

    public function IniReconciliation($date, $file, $user, $companyId)
    {
        //TODO: BEGIN-COMMIT TX
        //TODO: TOMAR EL ULTIMO DIA DEL MES
        $endDate = Carbon::createFromFormat('Y-m-d', $date);
        $startDate = Carbon::createFromFormat('Y-m-d', $date)->subDay();

        $this->createTablesIfExists($companyId);

        //ID to group reconciliation under ad ID
        $process = Str::random(9);

        $filePath = $this->saveIniReconciliationFile($file, $companyId);

        $externalInfo = $this->fileToArray($filePath);
        $localInfo = $this->fileToArray($filePath, 1);

        $this->insertLocalIni($localInfo, $companyId, $startDate, $endDate, $process);
        $this->insertExternalIni($externalInfo, $user, $companyId, $startDate, $endDate, $process);

        $balance =  $this->getProcessBalance($process, $companyId);

        $this->setReconciliationBalance($balance, $companyId);

        $itemsTableName = $this->getReconciliationItemTableName($companyId);
        $reconciliattionItems = (new ReconciliationItem($itemsTableName))
            ->where('process', $process)
            ->with('account')
            ->get();

        return $reconciliattionItems;
    }

    public function setReconciliationBalance($balance, $companyId)
    {
        $itemsTableName = $this->getReconciliationItemTableName($companyId);

        foreach ($balance as $key => $value) {
            $item = (new ReconciliationItem($itemsTableName))
                ->where('id', $value->accountId)
                ->first();

            $item->external_credit = $value->externalCredit;
            $item->external_debit = $value->externalDebit;
            $item->local_credit = $value->localCredit;
            $item->local_debit = $value->localDebit;
            $item->save();
        }
    }

    public function getProcessBalance($process, $companyId)
    {

        $itemsTableName = $this->getReconciliationItemTableName($companyId);
        $ids = (new ReconciliationItem($itemsTableName))
            ->where('process', $process)
            ->pluck('id')
            ->toArray();

        $externalValuesTableName = $this->getReconciliationExternalValuesTableName($companyId);
        // TODO: VALIDATE AND REMOVE
        // $externalBalance = DB::table($externalValuesTableName)
        //     ->select(DB::raw("accounts.id, SUM(valor_credito) as credit, SUM(valor_debito) as debit, numero_cuenta,
        //             banks.name, accounts.local_account, banks.name"))
        //     ->join('accounts', $externalValuesTableName . '.numero_cuenta', '=', 'accounts.bank_account')
        //     ->join('banks', 'accounts.bank_id', '=', 'banks.id')
        //     ->whereIn('item_id', $ids)
        //     ->where('accounts.company_id', '=', $companyId)
        //     ->groupBy('id', 'numero_cuenta', 'banks.name', 'accounts.local_account', 'bank_account')
        //     ->orderBy('banks.name', 'DESC')
        //     ->orderBy('numero_cuenta', 'ASC')
        //     ->get();

        // return $externalBalance;

        $localValuesTableName = $this->getReconciliationLocalValuesTableName($companyId);

        $balance = DB::table($localValuesTableName)
            ->select(DB::raw("
                accounts.id AS accountId, 
                SUM(" . $localValuesTableName . ".valor_credito) as localCredit, 
                SUM(" . $localValuesTableName . ".valor_debito) as localDebit,
                SUM(" . $externalValuesTableName . ".valor_credito) as externalCredit, 
                SUM(" . $externalValuesTableName . ".valor_debito) as externalDebit,"
                . $localValuesTableName . ".local_account"))
            ->join($externalValuesTableName, $localValuesTableName . '.local_account', '=', $externalValuesTableName . '.local_account')
            ->join('accounts', $localValuesTableName . '.local_account', '=', 'accounts.local_account')
            ->join('banks', 'accounts.bank_id', '=', 'banks.id')
            ->whereIn($localValuesTableName . '.item_id', $ids)
            ->where('accounts.company_id', '=', $companyId)
            ->groupBy('accountId', 'banks.name', 'accounts.local_account')
            ->orderBy('banks.name', 'DESC')
            ->orderBy('local_account', 'ASC')
            ->get();


        return $balance;
    }

    public function createTablesIfExists($companyId)
    {

        $ItemstableName = $this->getReconciliationItemTableName($companyId);
        if (!Schema::hasTable($ItemstableName)) {
            $this->createTableReconciliationItems($ItemstableName);
        }

        $externalValuesTableName = $this->getReconciliationExternalValuesTableName($companyId);
        if (!Schema::hasTable($externalValuesTableName)) {
            $this->createTableReconciliationExternalValues($externalValuesTableName, $companyId);
        }

        $localValuesTableName = $this->getReconciliationLocalValuesTableName($companyId);
        if (!Schema::hasTable($localValuesTableName)) {
            $this->createTableReconciliationLocalValues($localValuesTableName, $companyId);
        }

        try {
            Schema::table($externalValuesTableName, (function (Blueprint $table) use ($ItemstableName, $localValuesTableName) {
                $table->foreign('item_id')->references('id')->on($ItemstableName);
                $table->foreign('matched_id')->references('id')->on($localValuesTableName);
            }));

            Schema::table($localValuesTableName, (function (Blueprint $table) use ($ItemstableName, $externalValuesTableName) {
                $table->foreign('item_id')->references('id')->on($ItemstableName);
                $table->foreign('matched_id')->references('id')->on($externalValuesTableName);
            }));
        } catch (Exception $e) {
            if (!str_contains($e->getMessage(), 'Duplicate foreign key')) {
                throw new Exception($e->getMessage());
            }
        }
        try {
            Schema::table($localValuesTableName, (function (Blueprint $table) use ($ItemstableName, $externalValuesTableName) {
                $table->foreign('item_id')->references('id')->on($ItemstableName);
                $table->foreign('matched_id')->references('id')->on($externalValuesTableName);
            }));
        } catch (Exception $e) {
            if (!str_contains($e->getMessage(), 'Duplicate foreign key')) {
                throw new Exception($e->getMessage());
            }
        }
    }

    public function createReconciliationItem($account, $companyId, $startDate, $endDate, $process)
    {
        $tableName = $this->getReconciliationItemTableName($companyId);

        $itemsTable = new ReconciliationItem($tableName);

        $item = $itemsTable->where('account_id', $account->id)
            ->orderBy('start_date', 'ASC')
            ->first();

        if ($item && $item->type != ReconciliationItem::TYPE_INIT) {
            throw new Exception(`Ya existe una conciliación para la cuenta {$account->bank_account} del banco {$account->banks->name}`, 400);
        }


        if (!$item) {
            $now = Carbon::now();
            $newData = [
                'account_id' => $account->id,
                'process' => $process,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'external_debit' => 0,
                'external_debit' => 0,
                'external_debit' => 0,
                'external_credit' => 0,
                'local_debit' => 0,
                'local_credit' => 0,
                'external_balance' => 0,
                'local_balance' => 0,
                'difference' => 0,
                'status' => ReconciliationItem::OPEN_STATUS,
                'step' => ReconciliationItem::STEP_UPLOADED,
                'type' => ReconciliationItem::TYPE_INIT,
                'created_at' => $now,
                'updated_at' => $now
            ];

            $newItem = (new ReconciliationItem($tableName))->insertGetId($newData);
            return $newItem;
        } else {
            $item->process = $process;
            $item->save();
            return $item->id;
        }
    }

    public function insertLocalIni($localInfo, $companyId, $startDate, $endDate, $process)
    {
        $now = Carbon::now();
        $tableName = $this->getLocalTxTypeTableName($companyId);
        $localTxTypeTable = new LocalTxType($tableName);

        $localInsert = [];
        $accountNumber = $localInfo[0][0];

        $account = Account::where('local_account', $accountNumber)
            ->with('banks')
            ->first();

        $itemId = $this->createReconciliationItem($account, $companyId, $startDate, $endDate, $process);
        $itemsIdList[] = $itemId;

        if (!$account) {
            throw new Exception(`No existe la cuenta {$accountNumber}`);
        }

        foreach ($localInfo as $key => $row) {
            if (!$row || $row[3] == null) {
                continue;
            }
            if ($row[0] != $accountNumber) {
                $accountNumber = $row[0];
                $account = Account::where('local_account', $accountNumber)
                    ->with('banks')
                    ->first();

                if (!$account) {
                    throw new Exception(`No existe la cuenta {$accountNumber}`);
                }
                $itemId = $this->createReconciliationItem($account, $companyId, $startDate, $endDate, $process);
                $itemsIdList[] = $itemId;
            }

            $txTypeId = null;
            $txTypeName = null;

            $txType = $localTxTypeTable
                ->where('description', strtoupper($row[8]))
                ->first();

            if (!$txType) {
                throw new Exception('No existe una transacción con descripción: ' . $row[8], 400);
            }

            if ($txType) {
                $txTypeId = $txType->id;
                $txTypeName = $txType->tx;
            }

            $localInsert[] = [
                'item_id' => $itemId,
                'tx_type_id' => $txTypeId,
                'tx_type_name' => $txTypeName,
                'local_account' => $row[0],
                'cuenta_externa' => $row[3],
                'fecha_movimiento' => date('Y-m-d', strtotime($row[4])),
                'numero_comprobante' => $row[5],
                'referencia_1' => $row[6],
                'identificacion_tercero' => $row[7],
                'descripcion' => $row[8],
                'valor_debito' => $row[9],
                'valor_credito' => $row[10],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $tableName = $this->getReconciliationLocalValuesTableName($companyId);
        $localValuesTable =  new ReconciliationLocalValues($tableName);
        $localValuesTable->whereIn('item_id', $itemsIdList)->delete();

        $localValuesTable->insert($localInsert);
    }

    public function insertExternalIni($externalInfo, $user, $companyId, $startDate, $endDate, $process)
    {
        $now = Carbon::now();

        $externalInsert = [];
        $itemsIdList = [];
        $accountNumber = $externalInfo[0][2];

        $account = Account::where('bank_account', $accountNumber)
            ->with('banks')
            ->first();

        $itemId = $this->createReconciliationItem($account, $companyId, $startDate, $endDate, $process);
        $itemsIdList[] = $itemId;

        if (!$account) {
            throw new Exception(`No existe la cuenta {$accountNumber}`);
        }

        foreach ($externalInfo as $key => $row) {
            if (!$row || $row[3] == null) {
                continue;
            }
            if ($row[2] != $accountNumber) {
                $accountNumber = $row[2];
                $account = Account::where('bank_account', $accountNumber)
                    ->with('banks')
                    ->first();

                if (!$account) {
                    throw new Exception(`No existe la cuenta {$accountNumber}`);
                }
                $itemId = $this->createReconciliationItem($account, $companyId, $startDate, $endDate, $process);
                $itemsIdList[] = $itemId;
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

            $txType = ExternalTxType::where('description', 'like', trim($row[7]) . '%')
                ->where('type', 'COMPUESTO')
                ->first();

            if ($txType) {
                $txTypeId = $txType->id;
                $txTypeName = $txType->tx;
            }


            if (!$txTypeId) {
                throw new Exception('No existe una transacción con descripción: ' . $row[7], 400);
            }

            $externalInsert[] = [
                'item_id' => $itemId,
                'tx_type_id' => $txTypeId,
                'tx_type_name' => $txTypeName,
                'local_account' => $account->local_account,
                'numero_cuenta' => $row[2],
                'fecha_movimiento' => date('Y-m-d', strtotime($row[3])),
                'referencia_1' => $row[4],
                'referencia_2' => $row[5],
                'referencia_3' => $row[6],
                'descripcion' => $row[7],
                'valor_credito' => $row[8],
                'valor_debito' => $row[9],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $tableName = $this->getReconciliationExternalValuesTableName($companyId);
        $externalValuesTable =  new ReconciliationExternalValues($tableName);
        $externalValuesTable->whereIn('item_id', $itemsIdList)->delete();

        $externalValuesTable->insert($externalInsert);
    }

    public function fileToArray($filePath, $sheet = 0, $extenssion = 'Xlsx')
    {
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
        $spreadsheet = $reader->load($filePath);
        $spreadsheet->setActiveSheetIndex($sheet);
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
            }
            $data[] = $rows;
        }

        return $data;
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
            }
            $data[] = $rows;
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
    public function listTableForeignKeys($table)
    {
        $conn = Schema::getConnection()->getDoctrineSchemaManager();

        return array_map(function ($key) {
            return $key->getName();
        }, $conn->listTableForeignKeys($table));
    }

    public function getReconciliationItemTableName($companyId): string
    {
        return 'reconciliation_items_' . $companyId;
    }
    public function getReconciliationLocalValuesTableName($companyId): string
    {
        return 'reconciliation_local_values_' . $companyId;
    }
    public function getReconciliationExternalValuesTableName($companyId): string
    {
        return 'reconciliation_external_values_' . $companyId;
    }
    public function getLocalTxTypeTableName($companyId): string
    {
        return 'reconciliation_local_tx_types_' . $companyId;
    }

    public function saveIniReconciliationFile($file, $companyId)
    {

        $ext = $file->extension() == 'txt' ? 'csv' : $file->extension();
        $filePath = $companyId . '/reconciliation/initial.' . $ext;
        Storage::disk('reconciliation')->put($filePath, file_get_contents($file));

        return storage_path('reconciliation') . '/' . $filePath;
    }

    // TABLE CREATION
    public function createTableReconciliationItems(String $tableName)
    {

        Schema::create($tableName, function ($table) {
            $table->increments('id');
            $table->integer('account_id')->unsigned();
            $table->string('process');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('external_debit', 24, 2);
            $table->decimal('external_credit', 24, 2);
            $table->decimal('local_debit', 24, 2);
            $table->decimal('local_credit', 24, 2);
            $table->decimal('external_balance', 24, 2);
            $table->decimal('local_balance', 24, 2);
            $table->decimal('difference', 24, 2);
            $table->string('status')->default(ReconciliationItem::OPEN_STATUS);
            $table->string('step')->default(ReconciliationItem::STEP_UPLOADED);
            $table->string('type');
            $table->softDeletes();
            $table->timestamps();

            $table->index(['process', 'start_date', 'end_date']);
            $table->foreign('account_id')->references('id')->on('accounts');
        });
    }

    public function createTableReconciliationExternalValues($tableName, $companyId)
    {
        Schema::create($tableName, (function ($table) use ($companyId) {

            $itemsTableName = $this->getReconciliationItemTableName($companyId);
            $localValuesTableName = $this->getReconciliationLocalValuesTableName($companyId);

            $table->bigIncrements('id');
            $table->integer('item_id')->unsigned()->nullable();
            $table->boolean('matched')->default(false);
            $table->bigInteger('matched_id')->unsigned()->nullable();
            $table->integer('tx_type_id')->unsigned();
            $table->string('tx_type_name')->nullable();
            $table->string('descripcion')->comment = 'transaccion/descripcion';
            $table->string('local_account');
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
        }));
    }

    public function createTableReconciliationLocalValues($tableName, $companyId)
    {

        Schema::create($tableName, (function ($table) use ($companyId) {

            $table->bigIncrements('id');
            $table->integer('item_id')->unsigned()->nullable();
            $table->boolean('matched')->default(false);
            $table->bigInteger('matched_id')->unsigned()->nullable();
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
        }));
    }
}
