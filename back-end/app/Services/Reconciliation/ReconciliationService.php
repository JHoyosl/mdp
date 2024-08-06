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
use App\Models\ReconciliationHeader;
use App\Services\Account\AccountingService;
use App\Services\Account\SetAccountinTxService;
use App\Services\ThirdParties\ThirdPartiesService;

class ReconciliationService
{
    public const TYPE_LE = 'LE';
    public const TYPE_EL = 'EL';
    public const TYPE_LL = 'LL';
    public const TYPE_EE = 'EE';

    private ThirdPartiesService $thirdPartiesService;
    private AccountingService $accountingService;
    private SetAccountinTxService $setAccountinTxService;
    private AutomaticReconciliation $automaticReconciliation;

    function __construct(
        ThirdPartiesService $thirdPartiesService,
        AccountingService $accountingService,
        SetAccountinTxService $setAccountinTxService,
        AutomaticReconciliation $automaticReconciliation
    ) {
        $this->thirdPartiesService = $thirdPartiesService;
        $this->accountingService = $accountingService;
        $this->setAccountinTxService = $setAccountinTxService;
        $this->automaticReconciliation = $automaticReconciliation;
    }

    public function IniReconciliation($date, $file, $user, $companyId)
    {
        DB::beginTransaction();

        //TODO: TOMAR EL ULTIMO DIA DEL MES
        $endDate = Carbon::createFromFormat('Y-m-d', $date);
        $startDate = Carbon::createFromFormat('Y-m-d', $date)->subDay();

        //ID to group reconciliation under ad ID
        $process = Str::random(9);

        $filePath = $this->saveIniReconciliationFile($file, $companyId);

        $externalInfo = $this->fileToArray($filePath);
        $localInfo = $this->fileToArray($filePath, 1);

        $this->insertLocalIni($localInfo, $companyId, $startDate, $endDate, $process);
        $this->insertExternalIni($externalInfo, $user, $companyId, $startDate, $endDate, $process);

        $balance =  $this->getProcessBalance($process, $companyId);

        $this->setReconciliationBalance($balance, $companyId);

        DB::commit();

        return $this->getAccountProcessById($companyId, $process);
    }

    //TODO:  ORGANIZAR  LA LOGICA DEL DELETE
    public function deleteProcess($process, $companyId)
    {
        $itemsTableName = $this->getReconciliationItemTableName($companyId);
        $localValuesTableName = $this->getReconciliationLocalValuesTableName($companyId);
        $externalValuesTableName = $this->getReconciliationExternalValuesTableName($companyId);

        $itemsTable = new ReconciliationItem();
        $localValuesTable = new ReconciliationLocalValues($localValuesTableName);
        $externalValuesTable = new ReconciliationExternalValues($localValuesTableName);

        $itemsBuilder = $itemsTable->where('process', $process);
        $ids = $itemsBuilder->get()->map(function ($item) {
            return $item->id;
        });
        return $ids;
        DB::raw('SET FOREIGN_KEY_CHECKS = 0');
        $itemsBuilder->delete();
        $localValuesTable->whereIn('item_id', $ids)->delete();
        $externalValuesTable->whereIn('item_id', $ids)->delete();
        DB::raw('SET FOREIGN_KEY_CHECKS = 1');
        // $externalValuesTable->
        return $process;
    }

    public function autoProcess($process, $companyId)
    {
        $pitvotTableName = $this->getReconciliationPivotTableName($companyId);
        $itemTableName = $this->getReconciliationItemTableName($companyId);
        $itemTable = new ReconciliationItem();
        $itemTable->setTable($itemTableName);
        $items = $itemTable->where('process', $process)->with('account')->get();

        $accounts = $items->map(function ($value) {
            return $value->account->local_account;
        });

        // // CASOS CON REFERENCIA
        // $this->automaticReconciliation->case1($accounts, $companyId, $items[0]->start_date, $items[0]->end_date, $process);
        // $this->automaticReconciliation->case1b($accounts, $companyId, $items[0]->start_date, $items[0]->end_date, $process);
        // $this->automaticReconciliation->case1c($accounts, $companyId, $items[0]->start_date, $items[0]->end_date, $process);
        // $this->automaticReconciliation->case3($accounts, $companyId, $items[0]->start_date, $items[0]->end_date, $process);
        // $this->automaticReconciliation->case4($accounts, $companyId, $items[0]->start_date, $items[0]->end_date, $process);
        // $this->automaticReconciliation->case4b($accounts, $companyId, $items[0]->start_date, $items[0]->end_date, $process);
        // $this->automaticReconciliation->caseNomina($accounts, $companyId, $items[0]->start_date, $items[0]->end_date, $process);
        // $this->automaticReconciliation->case5($accounts, $companyId, $items[0]->start_date, $items[0]->end_date, $process);
        // $this->automaticReconciliation->case5b($accounts, $companyId, $items[0]->start_date, $items[0]->end_date, $process);

        // // TIPO DE TX
        // $this->automaticReconciliation->case2($accounts, $companyId, $items[0]->start_date, $items[0]->end_date, $process);
        // $this->automaticReconciliation->case2b($accounts, $companyId, $items[0]->start_date, $items[0]->end_date, $process);
        // $this->automaticReconciliation->case2c($accounts, $companyId, $items[0]->start_date, $items[0]->end_date, $process);
        // $this->automaticReconciliation->case2d($accounts, $companyId, $items[0]->start_date, $items[0]->end_date, $process);

        // $this->automaticReconciliation->case6($accounts, $companyId, $items[0]->start_date, $items[0]->end_date, $process);
        // $this->automaticReconciliation->case6b($accounts, $companyId, $items[0]->start_date, $items[0]->end_date, $process);
        // $this->automaticReconciliation->case6c($accounts, $companyId, $items[0]->start_date, $items[0]->end_date, $process);
        // $this->automaticReconciliation->case6d($accounts, $companyId, $items[0]->start_date, $items[0]->end_date, $process);

        // $this->automaticReconciliation->case7($accounts, $companyId, $items[0]->start_date, $items[0]->end_date, $process);
        // $this->automaticReconciliation->case7b($accounts, $companyId, $items[0]->start_date, $items[0]->end_date, $process);

        // $this->automaticReconciliation->case8($accounts, $companyId, $items[0]->start_date, $items[0]->end_date, $process);
        // $this->automaticReconciliation->case8b($accounts, $companyId, $items[0]->start_date, $items[0]->end_date, $process);

        // $this->automaticReconciliation->case9($accounts, $companyId, $items[0]->start_date, $items[0]->end_date, $process);
        // $this->automaticReconciliation->case9b($accounts, $companyId, $items[0]->start_date, $items[0]->end_date, $process);
        // $this->automaticReconciliation->case9c($accounts, $companyId, $items[0]->start_date, $items[0]->end_date, $process);
        // $this->automaticReconciliation->case9d($accounts, $companyId, $items[0]->start_date, $items[0]->end_date, $process);

        // $this->automaticReconciliation->case11($accounts, $companyId, $items[0]->start_date, $items[0]->end_date, $process);
        // $this->automaticReconciliation->case11b($accounts, $companyId, $items[0]->start_date, $items[0]->end_date, $process);
        // $this->automaticReconciliation->case11c($accounts, $companyId, $items[0]->start_date, $items[0]->end_date, $process);
        $this->automaticReconciliation->case11d($accounts, $companyId, $items[0]->start_date, $items[0]->end_date, $process);
        // $this->automaticReconciliation->case13($accounts, $companyId, $items[0]->start_date, $items[0]->end_date, $process);
        // $this->automaticReconciliation->case13b($accounts, $companyId, $items[0]->start_date, $items[0]->end_date, $process);

        // $this->automaticReconciliation->case5c($accounts, $companyId, $items[0]->start_date, $items[0]->end_date, $process);
        // $this->automaticReconciliation->case5d($accounts, $companyId, $items[0]->start_date, $items[0]->end_date, $process);

        // return $this->automaticReconciliation->case6($accounts, $companyId, $items[0]->start_date, $items[0]->end_date, $process);
    }




    public function newProcess($date, $accounts, $companyId, $user)
    {

        $items = $this->getReconciliationItems($companyId, $accounts);

        $this->checkThirdPartiesInfo($items, $companyId, $date);
        $this->checkAccountingInfo($companyId, $date);

        $items = $this->createReconciliationItem($items, $date);

        $items = $this->getInfoToReconciliate($companyId, $items, $date);

        DB::beginTransaction();

        $this->insertInfoToReconciliate($companyId,  $items);

        $balance = $this->getProcessBalance($items[0]->newProcess->process, $companyId);

        $this->setReconciliationBalance($balance, $companyId);

        // get array of local_account number
        $accountNumbers = array_map(function ($account) {
            return $account->local_account;
        }, $items);
        //set txType
        $this->setAccountinTxService->updateSimpleTx($companyId, $accountNumbers);
        $this->setAccountinTxService->updateTxByReference($companyId, $accountNumbers);


        DB::commit();

        return $this->getAccountProcessById($companyId, $items[0]->newProcess->process);
    }

    public function insertInfoToReconciliate($companyId, $items)
    {
        foreach ($items as $item) {
            $tableName = $this->getReconciliationItemTableName($companyId);
            $itemsTable =  new ReconciliationLocalValues($tableName);

            $newProcess = $itemsTable->insertGetId($item->newProcess);
            $item->newProcess = $itemsTable->where('id', $newProcess)->first();

            foreach ($item->accountingInfo as $row) {
                $accountingInfo[] = [
                    'item_id' => $item->newProcess->id,
                    'tx_type_id' => $row->tx_type_id,
                    'tx_type_name' => $row->tx_type_name,
                    'fecha_movimiento' => $row->fecha_movimiento,
                    'descripcion' => $row->descripcion,
                    'local_account' => $row->local_account,
                    'cuenta_externa' => $row->cuenta_externa,
                    'referencia_1' => $row->referencia_1,
                    'referencia_2' => $row->referencia_2,
                    'referencia_3' => $row->referencia_3,
                    'otra_referencia' => $row->otra_referencia,
                    'saldo_actual' => $row->saldo_actual,
                    'valor_debito' => $row->valor_debito,
                    'saldo_anterior' => $row->saldo_anterior,
                    'valor_credito' => $row->valor_credito,
                    'codigo_usuario' => $row->codigo_usuario,
                    'nombre_agencia' => $row->nombre_agencia,
                    'valor_debito_credito' => $row->valor_debito_credito,
                    'nombre_centro_costos' => $row->nombre_centro_costos,
                    'codigo_centro_costo' => $row->codigo_centro_costo,
                    'numero_comprobante' => $row->numero_comprobante,
                    'nombre_usuario' => $row->nombre_usuario,
                    'nombre_cuenta_contable' => $row->nombre_cuenta_contable,
                    'numero_cuenta_contable' => $row->numero_cuenta_contable,
                    'nombre_tercero' => $row->nombre_tercero,
                    'identificacion_tercero' => $row->identificacion_tercero,
                    'fecha_ingreso' => $row->fecha_ingreso,
                    'fecha_origen' => $row->fecha_origen,
                    'oficina_origen' => $row->oficina_origen,
                    'oficina_destino' => $row->oficina_destino,
                    'numero_lote' => $row->numero_lote,
                    'consecutivo_lote' => $row->consecutivo_lote,
                    'tipo_registro' => $row->tipo_registro,
                    'ambiente_origen' => $row->ambiente_origen,
                    'beneficiario' => $row->beneficiario,
                    'created_at' => Carbon::now(),
                ];
            }

            foreach ($item->thirdPartyInfo as $row) {
                $thirdPartyInfo[] = [
                    'item_id' => $item->newProcess->id,
                    'tx_type_id' => $row->tx_type_id,
                    'tx_type_name' => $row->tx_type_nametx_type_name,
                    'descripcion' => $row->descripcion,
                    'local_account' => $item->local_account,
                    'operador' => $row->operador,
                    'valor_credito' => $row->valor_credito,
                    'valor_debito' => $row->valor_debito,
                    'valor_debito_credito' => $row->valor_debito_credito,
                    'fecha_movimiento' => $row->fecha_movimiento,
                    'fecha_archivo' => $row->fecha_archivo,
                    'codigo_tx' => $row->codigo_tx,
                    'referencia_1' => $row->referencia_1,
                    'referencia_2' => $row->referencia_2,
                    'referencia_3' => $row->referencia_3,
                    'nombre_titular' => $row->nombre_titular,
                    'identificacion_titular' => $row->identificacion_titular,
                    'numero_cuenta' => $row->numero_cuenta,
                    'nombre_transaccion' => $row->nombre_transaccion,
                    'consecutivo_registro' => $row->consecutivo_registro,
                    'nombre_oficina' => $row->nombre_oficina,
                    'codigo_oficina' => $row->codigo_oficina,
                    'canal' => $row->canal,
                    'nombre_proveedor' => $row->nombre_proveedor,
                    'id_proveedor' => $row->id_proveedor,
                    'banco_destino' => $row->banco_destino,
                    'fecha_rechazo' => $row->fecha_rechazo,
                    'motivo_rechazo' => $row->motivo_rechazo,
                    'ciudad' => $row->ciudad,
                    'tipo_cuenta' => $row->tipo_cuenta,
                    'numero_documento' => $row->numero_documento,
                    'codigo_oficina' => $row->codigo_oficina,
                ];
            }
        }

        $tableName = $this->getReconciliationLocalValuesTableName($companyId);
        $localValuesTable =  new ReconciliationLocalValues($tableName);
        $localValuesTable->where('item_id', $items[0]->newProcess->id)->delete();

        foreach (array_chunk($accountingInfo, 500) as $t) {
            DB::table($tableName)->insert($t);
        }

        $tableName = $this->getReconciliationExternalValuesTableName($companyId);
        $externalValuesTable =  new ReconciliationExternalValues($tableName);
        $externalValuesTable->where('item_id', $items[0]->newProcess->id)->delete();

        foreach (array_chunk($thirdPartyInfo, 500) as $t) {
            DB::table($tableName)->insert($t);
        }
    }

    public function createReconciliationItem($items, $endDate, $type = ReconciliationItem::TYPE_PARTIAL)
    {
        //ID to group reconciliation under ad ID
        $process = Str::random(9);
        $now = Carbon::now()->toDateString();
        foreach ($items as $item) {
            $startDate = Carbon::parse($item->end_date)->addDay()->toDateString();

            $newData = [
                'account_id' => $item->id,
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
                'prev_external_balance' => $item->external_balance,
                'local_balance' => 0,
                'prev_local_balance' => $item->local_balance,
                'difference' => 0,
                'status' => ReconciliationItem::OPEN_STATUS,
                'step' => ReconciliationItem::STEP_SET_BALANCE,
                'type' => $type,
                'created_at' => $now,
                'updated_at' => $now
            ];
            $item['newProcess'] = $newData;
        }
        return $items;
    }

    public  function getInfoToReconciliate($companyId, $items, $date)
    {
        foreach ($items  as $item) {
            $accountingInfo = $this->accountingService
                ->getAccInfoToReconciliate($companyId, $item->local_account, $item->end_date, $date);
            if (count($accountingInfo) == 0) {
                throw new Exception("No accounting data for dates: {$item->end_date} {$date}");
            }
            $item['accountingInfo'] = $accountingInfo;

            $thirdPartyInfo = $this->thirdPartiesService
                ->getAccInfoToReconciliate($item->account_id, $item->end_date, $date);
            if (count($accountingInfo) == 0) {
                throw new Exception("No thirdParty data for dates: {$item->end_date} {$date}");
            }
            $item['thirdPartyInfo'] = $thirdPartyInfo;
        }
        return  $items;
    }

    public function getThirdPartiesIdsToReconciliate($companyId, $items, $date)
    {
        $thirdPartiesHeaderIds = [];

        foreach ($items as $value) {
            $headers = $this->thirdPartiesService->getThirdPartyHeadersByDate($companyId, $value->account_id, $date);
            $ids = [];
            foreach ($headers as $value) {
                $ids[] = $value->id;
            }
            $thirdPartiesHeaderIds[$value->account_id] = array_unique($ids);
        }

        return $thirdPartiesHeaderIds;
    }

    public function getAccHeadersIdsToReconciliate($companyId, $items, $date)
    {
        $accHeadersIds = [];
        foreach ($items as $value) {
            $headers = $this->accountingService->getAccHeadersByDate($companyId, $date);
            foreach ($headers as $header) {
                $accHeadersIds[] = $header->id;
            }
        }

        return array_unique($accHeadersIds);
    }

    public function getReconciliationItems($companyId, $accounts)
    {

        $itemsTableName = $this->getReconciliationItemTableName($companyId);
        $itemsTable = new ReconciliationItem();
        $itemsTable->setTable($itemsTableName);
        $items = [];
        $invalidItems = [];
        foreach ($accounts as $value) {
            $item = $itemsTable->where($itemsTableName . '.account_id', $value)
                ->join('accounts', $itemsTableName . '.account_id', 'accounts.id')
                ->orderBy($itemsTableName . '.created_at')
                ->first();

            if ($item->step != ReconciliationItem::STEP_DONE) {
                $invalidItems[] = [
                    'bankAccount' => $item->bank_account,
                    'localAccount' => $item->local_account,
                    'step' => $item->step,
                    'error' => 'Step should be  done'
                ];
                continue;
            }

            $items[] = $item;
        }
        if (count($invalidItems) > 0) {
            throw new Exception(json_encode($invalidItems), 400);
        }

        return $items;
    }

    public function checkAccountingInfo($companyId, $date)
    {
        $carbonDate = Carbon::parse($date);
        $accHeader = $this->accountingService->getLastHeader($companyId);
        if (!$accHeader) {
            throw new  Exception('No existe información contable');
        }
        $endDate = Carbon::parse($accHeader->end_date);
        if (!$carbonDate->lte($endDate)) {
            throw new  Exception('No existe información contable para la fecha .' . $date);
        }
    }

    public function checkThirdPartiesInfo($items, $companyId, $date)
    {
        $carbonDate = Carbon::parse($date);
        $invalidDates = [];
        foreach ($items as $item) {
            $header = $this->thirdPartiesService->getLastHeaderByAccount($item->account_id, $companyId);
            if (!$header) {
                $invalidDates[] = [
                    'endDate' => null,
                    'bankAccount' => $item->bank_account,
                    'localAccount' => $item->local_account,
                    'error' => 'No data for account'
                ];
                continue;
            }
            $endDate = Carbon::parse($header->end_date);
            if (!$carbonDate->lte($endDate)) {
                $invalidDates[] = [
                    'endDate' => $header->end_date,
                    'bankAccount' => $item->bank_account,
                    'localAccount' => $item->local_account,
                    'error' => 'No data for dates'
                ];
            }
        }

        if (count($invalidDates) > 0) {
            throw new Exception(json_encode($invalidDates), 400);
        }
    }

    public function getAccountResume($companyId)
    {
        $itemsTableName = $this->getReconciliationItemTableName($companyId);
        $itemsTable = new ReconciliationItem();
        $itemsTable->setTable($itemsTableName);

        if (is_Null($itemsTable->first())) {
            return collect([]);
        }

        $accounts = Account::where('company_id', $companyId)
            ->join('banks', 'accounts.bank_id', 'banks.id')
            ->leftjoin($itemsTableName . ' AS items', 'accounts.id', 'items.account_id')
            ->select(
                'accounts.id AS accountId',
                'accounts.bank_id',
                'accounts.bank_account',
                'accounts.local_account',
                'banks.name',
                'items.process',
                'items.start_date',
                'items.end_date',
                'items.external_debit',
                'items.external_credit',
                'items.local_debit',
                'items.local_credit',
                'items.external_balance',
                'items.local_balance',
                'items.difference',
                'items.type',
                'items.status',
                'items.step',
            )
            ->orderBy('start_date', 'desc')
            ->get();


        $data = $accounts->reduce(function ($acc, $item) {
            if (!$acc) {
                $acc[] = $item;
                return $acc;
            }
            foreach ($acc as $row) {
                if ($row->accountId == $item->accountId) {
                    return $acc;
                }
            }
            $acc[] = $item;
            return Collect($acc);
        });

        return $data;
    }

    public function setBalance($companyId, $balanceInfo, $process)
    {
        $itemsTableName = $this->getReconciliationItemTableName($companyId);
        $itemsIds = [];
        foreach ($balanceInfo as $value) {
            $itemsIds[] = $value['id'];
        }

        $itemsTable = new ReconciliationItem();
        $itemsTable->setTable($itemsTableName);
        $items = $itemsTable->where($itemsTableName . '.process', $process)
            ->whereIn('id', $itemsIds)
            ->get();
        $invalidItems = [];
        foreach ($items as $item) {
            foreach ($balanceInfo as $balance) {
                if ($item->id == $balance['id']) {
                    $item->external_balance = $balance['externalBalance'];
                    $item->local_balance = $balance['localBalance'];

                    $externalDifference = $item->prev_external_balance +
                        $item->external_credit - $item->external_debit - $item->external_balance;
                    $localDifference = $item->prev_local_balance +
                        $item->local_debit - $item->local_credit - $item->local_balance;
                    $difference = abs(number_format($externalDifference, 2)) + abs(number_format($localDifference, 2));

                    $item->difference = $difference;
                    $item->step =  ReconciliationItem::STEP_MANUAL;
                    if ($item->difference != 0) {
                        $invalidItems[] = $item;
                    }
                }
            }
        }
        if (count($invalidItems) > 0) {
            $invalid = json_encode($invalidItems);
            throw new Exception("Error en las diferencias {$invalid}", 400);
        }
        foreach ($items as $item) {
            $item->save();
        }

        return $this->getAccountProcessById($companyId, $process);
    }

    public function setInitBalance($companyId, $balanceInfo, $process)
    {
        $itemsTableName = $this->getReconciliationItemTableName($companyId);
        $itemsIds = [];
        foreach ($balanceInfo as $value) {
            $itemsIds[] = $value['id'];
        }

        $itemsTable = new ReconciliationItem();
        $itemsTable->setTable($itemsTableName);
        $items = $itemsTable->where($itemsTableName . '.process', $process)
            ->whereIn('id', $itemsIds)
            ->get();

        $invalidItems = [];
        foreach ($items as $item) {
            foreach ($balanceInfo as $balance) {
                if ($item->id == $balance['id']) {
                    $item->local_balance = $balance['localBalance'];
                    $item->external_balance = $balance['externalBalance'];
                    $item->difference = $this->balanceDifference($item);
                    $item->step =  ReconciliationItem::STEP_DONE;
                    $item->status =  ReconciliationItem::CLOSE_STATUS;
                    if ($item->difference != 0) {
                        $invalidItems[] = $item;
                    }
                }
            }
        }

        if (count($invalidItems) > 0) {
            $invalid = json_encode($invalidItems);
            throw new Exception("Error en las diferencias {$invalid}", 400);
        }
        foreach ($items as $item) {
            $item->save();
        }

        return $this->getAccountProcessById($companyId, $process);
    }

    public function getAccountProcessById($companyId, $process)
    {
        $ItemstableName = $this->getReconciliationItemTableName($companyId);

        $items = Account::join($ItemstableName, 'accounts.id', $ItemstableName . '.account_id')
            ->join('banks', 'banks.id', 'accounts.bank_id')
            ->where('process', $process)
            ->orderBy('start_date', 'DESC')
            ->orderBy('account_id', 'DESC')
            ->select(
                $ItemstableName . '.id as id',
                $ItemstableName . ".process",
                $ItemstableName . ".start_date",
                $ItemstableName . ".end_date",
                $ItemstableName . ".external_debit",
                $ItemstableName . ".external_credit",
                $ItemstableName . ".local_debit",
                $ItemstableName . ".local_credit",
                $ItemstableName . ".external_balance",
                $ItemstableName . ".prev_external_balance",
                $ItemstableName . ".local_balance",
                $ItemstableName . ".prev_local_balance",
                $ItemstableName . ".difference",
                $ItemstableName . ".status",
                $ItemstableName . ".step",
                $ItemstableName . ".type",
                "accounts.id as account_id",
                "accounts.bank_id",
                "accounts.local_account",
                "accounts.bank_account",
                "banks.name",
                "banks.nit",
                "banks.currency",
            )
            ->get();
        return $items;
    }

    public function getAccountProcess($companyId)
    {

        $itemsTableName = $this->getReconciliationItemTableName($companyId);
        if (!Schema::hasTable($itemsTableName)) {
            return [];
        }
        $itemsTable = new ReconciliationItem();
        $itemsTable->setTable($itemsTableName);
        return $itemsTable->join('accounts', $itemsTableName . '.account_id', 'accounts.id')
            ->join('banks', 'banks.id', 'accounts.bank_id')
            ->where('company_id', $companyId)
            ->orderBy('start_date', 'DESC')
            ->orderBy('account_id', 'DESC')
            ->get();
        $items = Account::join($itemsTableName, 'accounts.id', $itemsTableName . '.id')
            ->join('banks', 'banks.id', 'accounts.bank_id')
            ->where('company_id', $companyId)
            ->orderBy('start_date', 'DESC')
            ->orderBy('account_id', 'DESC')
            ->get();
        return $items;
    }

    public function getReconciliationAccounts($companyId)
    {
        $itemsTableName = $this->getReconciliationItemTableName($companyId);
        $accounts = Account::where('company_id', $companyId)
            ->leftJoin($itemsTableName, 'accounts.id', $itemsTableName . '.account_id')
            ->get();

        return $accounts;
    }

    public function setReconciliationBalance($balance, $companyId)
    {
        $itemsTableName = $this->getReconciliationItemTableName($companyId);

        foreach ($balance as $value) {
            $item = (new ReconciliationItem())
                ->setTable($itemsTableName)
                ->where('id', $value['item_id'])
                ->first();

            $item->external_credit = $value['externalCredit'];
            $item->external_debit = $value['externalDebit'];
            $item->local_credit = $value['localCredit'];
            $item->local_debit = $value['localDebit'];
            $item->difference = $this->balanceDifference($item);
            $item->save();
        }
    }

    public function getProcessBalance($process, $companyId)
    {

        $itemsTableName = $this->getReconciliationItemTableName($companyId);
        $ids = (new ReconciliationItem())
            ->setTable($itemsTableName)
            ->where('process', $process)
            ->pluck('id')
            ->toArray();

        $externalValuesTableName = $this->getReconciliationExternalValuesTableName($companyId);
        $eBalance = DB::table($externalValuesTableName)
            ->select(DB::raw(
                "
                {$externalValuesTableName}.item_id, 
                {$externalValuesTableName}.local_account, 
                SUM({$externalValuesTableName}.valor_credito) as externalCredit, 
                SUM({$externalValuesTableName}.valor_debito) as externalDebit"
            ))
            ->whereIn("{$externalValuesTableName}.item_id", $ids)
            ->groupBy("{$externalValuesTableName}.local_account", "{$externalValuesTableName}.item_id")
            ->orderBy('local_account', 'ASC')
            ->get();

        $localValuesTableName = $this->getReconciliationLocalValuesTableName($companyId);
        $lBalance = DB::table($localValuesTableName)
            ->select(DB::raw(
                "
                {$localValuesTableName}.item_id, 
                {$localValuesTableName}.local_account, 
                SUM({$localValuesTableName}.valor_credito) as localCredit, 
                SUM({$localValuesTableName}.valor_debito) as localDebit"
            ))
            ->whereIn("{$localValuesTableName}.item_id", $ids)
            ->groupBy("{$localValuesTableName}.local_account", "{$localValuesTableName}.item_id")
            ->orderBy('local_account', 'ASC')
            ->get();

        $balance = [];

        foreach ($eBalance as $external) {
            foreach ($lBalance as $local) {
                if ($external->local_account == $local->local_account) {
                    $balance[] = [
                        'item_id' => $external->item_id,
                        'localCredit' => $local->localCredit ?? 0,
                        'localDebit' => $local->localDebit ?? 0,
                        'externalCredit' => $external->externalCredit ?? 0,
                        'externalDebit' => $external->externalDebit ?? 0,
                    ];
                }
            }
        }

        return $balance;
    }

    public function createInitReconciliationItem($account, $companyId, $startDate, $endDate, $process)
    {
        $itemsTableName = $this->getReconciliationItemTableName($companyId);

        $itemsTable = new ReconciliationItem();
        $itemsTable->setTable($itemsTableName);
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

            $newItem = (new ReconciliationItem())
                ->setTable($itemsTableName)
                ->insertGetId($newData);
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

        $itemId = $this->createInitReconciliationItem($account, $companyId, $startDate, $endDate, $process);
        $itemsIdList[] = $itemId;

        if (!$account) {
            throw new Exception(`No existe la cuenta {$accountNumber}`);
        }

        foreach ($localInfo as $key => $row) {
            if (!$row || !isset($row[3]) || $row[3] == null) {
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
                $itemId = $this->createInitReconciliationItem($account, $companyId, $startDate, $endDate, $process);
                $itemsIdList[] = $itemId;
            }

            $txTypeId = null;
            $txTypeName = null;

            $txType = $localTxTypeTable->select(
                DB::raw(
                    "id,
                    UPPER(description) as description,
                    tx,
                    reference,
                    sign,
                    type,
                    deleted_at,
                    created_at,
                    updated_at"
                )
            )->where('description', strtoupper($row[8]))
                ->orWhereRaw("UPPER('" . $row[8] . "') LIKE CONCAT(description, '%')")
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

        $itemId = $this->createInitReconciliationItem($account, $companyId, $startDate, $endDate, $process);
        $itemsIdList[] = $itemId;

        if (!$account) {
            throw new Exception(`No existe la cuenta {$accountNumber}`);
        }

        foreach ($externalInfo as $key => $row) {
            if (!$row || !isset($row[3]) || $row[3] == null) {
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
                $itemId = $this->createInitReconciliationItem($account, $companyId, $startDate, $endDate, $process);
                $itemsIdList[] = $itemId;
            }

            $txTypeId = null;
            $txTypeName = null;

            $txType = ExternalTxType::select(
                DB::raw(
                    "id,
                    UPPER(description) as description,
                    tx,
                    reference,
                    sign,
                    type,
                    deleted_at,
                    created_at,
                    updated_at"
                )
            )->where('description', strtoupper($row[7]))
                ->orWhereRaw("UPPER('" . $row[7] . "') LIKE CONCAT(description, '%')")
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

    public function referencesFileUpload($files, $process, $companyId)
    {

        return count($files);
    }

    public function fileToArray($filePath, $sheet = 0, $extenssion = 'Xlsx')
    {
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
        $spreadsheet = $reader->load($filePath);
        $spreadsheet->setActiveSheetIndex($sheet);
        $worksheet = $spreadsheet->getActiveSheet();

        $startRow = 2;

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

    // TODO:REMOVER, VALIDAR ANTES
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
        $itemsTableName = $this->getReconciliationItemTableName($companyId);
        $conciliarHeaderTable = 'conciliar_headers_' . $companyId;

        $itemsTable = new ReconciliationItem();
        $itemsTable->setTable($itemsTableName);
        $headers = new ReconciliationHeader($conciliarHeaderTable);

        $openHeader = $headers->where('status', ReconciliationItem::OPEN_STATUS)
            ->orderBy('id', 'desc')->first();


        $openItemTable = $itemsTable->where('header_id', '=', $openHeader->id)
            ->where('account_id', '=', $accountId)
            ->first();

        $lastBalance = $this->getLastAccountBalance($companyId, $accountId);

        // return $this->localDifference();
        return [$externalBalance, $localBalance, $accountId, $companyId];
    }

    public function localDifference(ReconciliationItem $itemsTable)
    {
        $diference = $itemsTable->external_balance +
            $itemsTable->debit_local -
            $itemsTable->external_credit +
            $itemsTable->external_debit -
            $itemsTable->local_debit -
            $itemsTable->local_balance;


        return $diference;

        // $calcValue = $lastLocalBalance + $itemsTable->debit_local - $itemsTable->credit_local;
        // $diference = $calcValue - $localBalance;
        // return $diference;
    }

    public function getLastAccountBalance($companyId, $accountId)
    {

        $conciliarHeaderTableName = 'conciliar_headers_' . $companyId;
        $conciliarItemsTableName = 'conciliar_items_' . $companyId;
        $conciliarHeaderTable = new ReconciliationItem();

        $conciliarHeaderClose = $conciliarHeaderTable->where('status', '=', ReconciliationItem::CLOSE_STATUS)
            ->orderBy('id', 'desc')
            ->first();

        $conciliarItemsTable = new ReconciliationItem();

        $conciliarItemsClose = $conciliarItemsTable
            ->where('header_id', '=', $conciliarHeaderClose->id)
            ->where('account_id', '=', $accountId)
            ->first();

        return [
            "balanceExterno" => $conciliarItemsClose->balance_externo,
            "balanceLocal" => $conciliarItemsClose->balance_local
        ];
    }

    public function delete($process, $companyId)
    {
        $itemTableName = $this->getReconciliationItemTableName($companyId);
        $externalTableName = $this->getReconciliationExternalValuesTableName($companyId);
        $localTableName = $this->getReconciliationExternalValuesTableName($companyId);

        $externalTable = new ReconciliationExternalValues($externalTableName);
        $localTable = new ReconciliationLocalValues($localTableName);

        $items = (new ReconciliationItem())
            ->where('process', $process)
            ->get();
        DB::beginTransaction();

        foreach ($items as $value) {
            $externalTable->where('item_id', $value->id)->delete();
            $localTable->where('item_id', $value->id)->delete();
        }
        $items = (new ReconciliationItem())
            ->where('process', $process)
            ->delete();

        DB::commit();
        return $items;
    }

    // HELPERS
    public function hasReconciliationBefore($startDate, $endDate, $companyId, $accountId = NULL)
    {
        $itemTableName = $this->getReconciliationItemTableName($companyId);
        $itemsTable = new ReconciliationItem();
        if ($accountId !=  NULL) {
            $item = $itemsTable
                ->where('account_id', $accountId)
                ->where('type', '!=', ReconciliationItem::TYPE_INIT)
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('start_date', [$startDate, $endDate])
                        ->orWhereBetween('end_date', [$startDate, $endDate]);
                })->first();

            return $item;
        }

        $item = $itemsTable
            ->where('type', '!=', ReconciliationItem::TYPE_INIT)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate]);
            })->first();

        return $item;
    }

    public function queryByRef($refLocal, $refExternal, $localTable, $externalTableName, $localTableName)
    {

        $ref = $localTable->leftJoin($externalTableName, function ($join)
        use ($refLocal, $refExternal, $localTableName, $externalTableName) {

            $join->on($localTableName . '.fecha_movimiento', $externalTableName . '.fecha_movimiento');
            $join->on($localTableName . '.local_account', $externalTableName . '.local_account');
            $join->on($localTableName . '.' . $refLocal, $externalTableName . '.' . $refExternal);
        })
            ->select(
                $localTableName . '.id as localId',
                $externalTableName . '.id as externalId',
                $localTableName . '.fecha_movimiento',
                $localTableName . '.local_account as cuenta_libros',
                $localTableName . '.cuenta_externa as cuenta_bancos',
                $localTableName . '.valor_debito as deb_libros',
                $externalTableName . '.valor_credito as cred_bancos',
                $localTableName . '.valor_credito as cred_libros',
                $externalTableName . '.valor_debito as deb_bancos',
                $localTableName . '.referencia_1 as lReferencia_1',
                $externalTableName . '.referencia_1 as eReferencia_1',
                $externalTableName . '.referencia_2 as eReferencia_2',
                $externalTableName . '.referencia_3 as eReferencia_3',

            )
            // ->whereNotIn('localId', $ids)
            ->whereNotNull($externalTableName . '.local_account')
            ->get();

        return $ref;
    }

    public function getProcessStep($process, $companyId)
    {
        $tableName = $this->getReconciliationItemTableName($companyId);
        $itemTable = new ReconciliationItem();
        $item = $itemTable->where('process', $process)->first();
        return $item->step;
    }

    public function setAccountingProcessItem($accountingInfo)
    {
    }

    public function getAccountingMaxDate($companyId)
    {
        $itemsTableName = $this->getReconciliationItemTableName($companyId);
        $table = new ReconciliationItem();
        $info = $table->orderBy('end_date', 'DESC')->first();
        return $info->end_date;
    }

    public function  getMaxDate($items)
    {
        $maxDate = null;
        foreach ($items as $item) {
            if (!$maxDate) {
                $maxDate = Carbon::parse($item->end_date);
                continue;
            }
            $date = carbon::parse($item->end_date);
            if ($date->gt($maxDate)) {
                $maxDate = $date;
            }
        }
        return  $maxDate;
    }

    public function balanceSum(ReconciliationItem $item)
    {
        return $item->external_balance +
            $item->external_debit -
            $item->external_credit -
            $item->local_credit +
            $item->local_debit;
    }

    public function balanceDifference(ReconciliationItem $item)
    {
        $sum = $this->balanceSum($item);
        $difference = $sum - $item->local_balance;
        return str_replace(',', '', number_format($difference, 2));
    }

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
    public function getReconciliationPivotTableName($companyId): string
    {
        return 'reconciliation_pivot_' . $companyId;
    }
    public function getLocalTxTypeTableName($companyId): string
    {
        return 'reconciliation_local_tx_types_' . $companyId;
    }

    public function getThirdPartyItemsTableName($companyId)
    {
        return 'third_parties_items_' . $companyId;
    }

    public function getAccountingItemsTableName($companyId)
    {
        return 'accounting_items_' . $companyId;
    }

    public function saveIniReconciliationFile($file, $companyId)
    {

        $ext = $file->extension() == 'txt' ? 'csv' : $file->extension();
        $filePath = $companyId . '/reconciliation/initial.' . $ext;
        Storage::disk('reconciliation')->put($filePath, file_get_contents($file));

        return storage_path('reconciliation') . '/' . $filePath;
    }

    // TABLE CREATION
    public function createTablesIfExists($companyId)
    {
        $ItemstableName = $this->getReconciliationItemTableName($companyId);
        $externalValuesTableName = $this->getReconciliationExternalValuesTableName($companyId);
        $localValuesTableName = $this->getReconciliationLocalValuesTableName($companyId);
        $reconciliationPivot =  $this->getReconciliationPivotTableName($companyId);

        if (!Schema::hasTable($ItemstableName)) {
            $this->createTableReconciliationItems($ItemstableName);
        }
        // external values
        if (!Schema::hasTable($externalValuesTableName)) {
            $this->createTableReconciliationExternalValues($externalValuesTableName, $ItemstableName);
        }
        //local values
        if (!Schema::hasTable($localValuesTableName)) {
            $this->createTableReconciliationLocalValues($localValuesTableName, $ItemstableName);
        }
        //pivot table
        if (!Schema::hasTable($reconciliationPivot)) {
            $this->createTableReconciliationPivot($reconciliationPivot);
        }

        // try {
        //     Schema::table($externalValuesTableName, (function (Blueprint $table) use ($ItemstableName, $localValuesTableName) {
        //         $table->foreign('item_id')->references('id')->on($ItemstableName);
        //     }));

        //     Schema::table($localValuesTableName, (function (Blueprint $table) use ($ItemstableName, $externalValuesTableName) {
        //         $table->foreign('item_id')->references('id')->on($ItemstableName);
        //     }));
        // } catch (Exception $e) {
        //     if (!str_contains($e->getMessage(), 'Duplicate foreign key')) {
        //         throw new Exception($e->getMessage());
        //     }
        // }
        // try {
        //     Schema::table($localValuesTableName, (function (Blueprint $table) use ($ItemstableName, $externalValuesTableName) {
        //         $table->foreign('item_id')->references('id')->on($ItemstableName);
        //         $table->foreign('matched_id')->references('id')->on($externalValuesTableName);
        //     }));
        // } catch (Exception $e) {
        //     if (!str_contains($e->getMessage(), 'Duplicate foreign key')) {
        //         throw new Exception($e->getMessage());
        //     }
        // }
    }

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
            $table->decimal('prev_external_balance', 24, 2)->default(0);
            $table->decimal('local_balance', 24, 2);
            $table->decimal('prev_local_balance', 24, 2)->default(0);
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

    public function createTableReconciliationExternalValues($tableName, $itemsTableName)
    {
        Schema::create($tableName, (function (Blueprint $table) use ($itemsTableName) {

            $table->bigIncrements('id');
            $table->integer('item_id')->unsigned()->nullable();
            $table->integer('tx_type_id')->unsigned()->nullable();
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
            $table->foreign('item_id')->references('id')->on($itemsTableName)->onDelete('cascade');
        }));
    }

    public function createTableReconciliationLocalValues($tableName, $itemsTableName)
    {

        Schema::create($tableName, (function (Blueprint $table) use ($itemsTableName) {

            $table->bigIncrements('id');
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

            $table->timestamps();

            $table->foreign('item_id')->references('id')->on($itemsTableName)->onDelete('cascade');
        }));
    }

    public function createTableReconciliationPivot($pivotTableName)
    {

        Schema::create($pivotTableName, function (Blueprint $table) {
            $table->foreignId('source_id');
            $table->foreignId('target_id');
            $table->string('type');
            $table->string('case');
            $table->string('process');
            $table->primary(['source_id', 'target_id']);
        });
    }
}
