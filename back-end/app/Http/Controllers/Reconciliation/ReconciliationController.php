<?php

namespace App\Http\Controllers\Reconciliation;


use Schema;
use Exception;
use App\Models\Account;
use App\Models\Company;
use App\Models\LocalTxType;
use Illuminate\Http\Request;
use App\Models\ExternalTxType;
use App\Models\ReconciliationItem;
use Illuminate\Support\Facades\DB;
use App\Models\ReconciliationHeader;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Session;

use Illuminate\Support\Facades\Storage;
use App\Models\ReconciliationLocalValues;
use App\Models\ReconciliationExternalValues;
use App\Services\Reconciliation\ReconciliationService;
use App\Services\Reconciliation\UploadConciliarContableService;
use App\Services\Reconciliation\UploadConciliationExternalService;
use App\Traits\TableNamming;

ini_set('memory_limit', '1000M');


set_time_limit(-1);

define("CONTABLE_COLS", 17);
define("RECAUDO_COLS", 13);

class ReconciliationController extends ApiController
{
    use TableNamming;
    protected $conciliar_headers_table = '';
    protected $conciliar_items_table = '';
    protected $conciliar_items_tmp_table = '';
    protected $conciliar_external_values_table = '';
    protected $conciliar_tmp_external_values_table = '';
    protected $conciliar_local_values_table = '';
    protected $conciliar_tmp_local_values_table = '';
    protected $conciliar_local_tx_type = '';
    protected $conciliar_external_tx_type = 'external_tx_types';

    protected UploadConciliarContableService $uploadConciliarContableService;
    protected UploadConciliationExternalService $uploadConciliationExternalService;
    protected ReconciliationService $reconciliationService;

    private $user;
    private $companyId;

    public function __construct(
        UploadConciliarContableService $uploadConciliarContableService,
        UploadConciliationExternalService $uploadConciliationExternalService,
        ReconciliationService $reconciliationService
    ) {
        $this->reconciliationService = $reconciliationService;
        $this->uploadConciliarContableService = $uploadConciliarContableService;
        $this->uploadConciliationExternalService = $uploadConciliationExternalService;

        $this->middleware('auth:api');
        $this->middleware(function ($request, $next) {

            $user = Auth::user();
            $this->user = $user;
            $this->companyId = $this->user->current_company;

            if ($this->companyId) {
                $this->reconciliationService->createTablesIfExists($this->companyId);
            }
            return $next($request);
        });
    }

    function init($user)
    {

        $this->conciliar_headers_table = 'conciliar_headers_' . $user->current_company;
        $this->conciliar_items_table = 'conciliar_items_' . $user->current_company;
        $this->conciliar_items_tmp_table = 'conciliar_tmp_items_' . $user->current_company;
        $this->conciliar_external_values_table = 'conciliar_external_values_' . $user->current_company;
        $this->conciliar_tmp_external_values_table = 'conciliar_tmp_external_values_' . $user->current_company;
        $this->conciliar_local_values_table = 'conciliar_local_values_' . $user->current_company;
        $this->conciliar_tmp_local_values_table = 'conciliar_tmp_local_values_table_' . $user->current_company;
        $this->conciliar_local_tx_type = 'conciliar_local_tx_type_' . $user->current_company;
        $this->conciliar_external_tx_type = 'external_tx_types';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $user = Auth::user();

        $headers = new ReconciliationHeader($this->conciliar_headers_table);

        return $headers->get();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {

            $this->reconciliationService->delete($id, $this->companyId);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage(), 400);
        }

        return $this->showMessage('Success', 200);
    }

    public function autoReconciliation(Request $request)
    {

        return $this->reconciliationService->autoProcess($request->process, $this->companyId);
    }

    public function deleteProcess(Request  $request)
    {
        return $this->reconciliationService->deleteProcess($request->process, $this->companyId);
    }

    public function startNewProcess(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required',
            'accounts' => 'required'
        ]);

        $accounts = json_decode($request->accounts, true);

        if (!$accounts) {
            return $this->errorResponse('Invalid Json', 400);
        }

        try {
            $items = $this->reconciliationService->newProcess(
                $request->date,
                $accounts,
                $this->companyId,
                $this->user
            );
            return $this->showAll($items);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function getAccountResume()
    {

        $accounts = $this->reconciliationService->getAccountResume($this->companyId);
        return $this->showAll($accounts);
    }

    public function getAccountProcessById(String $process)
    {
        $accounts = $this->reconciliationService
            ->getAccountProcessById($this->companyId, $process);
        return $this->showAll($accounts);
    }

    public function getAccountProcess()
    {

        $data = $this->reconciliationService->getAccountProcess($this->companyId);
        return $this->showAll($data);
    }

    public function iniReconciliation(Request $request)
    {
        
        $request->validate([
            'file' => 'required',
            'date' => 'required'
        ]);

        try {
            $initReconciliation = $this->reconciliationService->IniReconciliation(
                $request->date,
                $request->file,
                $this->user,
                $this->companyId
            );

            return $this->showAll($initReconciliation);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function getReconciliationAccounts()
    {
        $accounts = $this->reconciliationService->getReconciliationAccounts($this->companyId);
        return $this->showAll($accounts);
    }

    public function setBalance(Request $request)
    {
        $validated = $request->validate([
            'balance' => 'required',
            'process' => 'required'
        ]);

        
        $balanceInfo = json_decode($request->balance, true);

        if (!$balanceInfo) {
            return $this->errorResponse('Invalid Json', 400);
        }
        
        $step = $this->reconciliationService->getProcessStep($request->process, $this->companyId);

        try {
            switch ($step) {
                case ReconciliationItem::STEP_UPLOADED:
                    return $this->reconciliationService->setInitBalance($this->companyId, $balanceInfo, $request->process);
                case ReconciliationItem::STEP_SET_BALANCE:
                    return $this->reconciliationService->setBalance($this->companyId, $balanceInfo, $request->process);
                default:
                    return $this->errorResponse('NO STEP FOUND', 400);
            }
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function hasReconciliationBefore(Request $request)
    {

        $validated = $request->validate([
            'endDate' => 'required',
            'accountId' => 'required'
        ]);
        $item = $this->reconciliationService->hasReconciliationBefore($request->accountId, $request->endDate, $this->companyId);
        return $item;
    }
    // TODO: OLD REMOVE

    public function createTablesInit()
    {
        // DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        // Schema::dropIfExists($this->conciliar_headers_table);
        // $this->createTableConciliarHeaders();
        // DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->createTableConciliarHeaders();
        $this->createTableConciliarLocalTxType();
        $this->createTableReconciliationItems();
        $this->createTableReconciliationExternalValues();
        $this->createTableConciliarLocalValues();

        return true;
    }

    private function getLastConciliarHeader()
    {
        $conciliarModel = new ReconciliationHeader($this->conciliar_headers_table);

        $conciliarTable = $conciliarModel->where('status', '=', ReconciliationHeader::OPEN_STATUS)
            ->first();

        return $conciliarTable;
    }

    public function closeIniConciliar(Request $request)
    {

        $user = Auth::user();
        // return storage_path('app/conciliation/');
        if (!is_dir(storage_path('conciliation/' . $user->current_company . '/'))) {

            mkdir(storage_path('conciliation/' . $user->current_company . '/'), 0775, true);
        }

        $info = $request->all();

        $infoArray = json_decode($info['info'], true);
        $fechaCierre = $info['fecha_cierre'];

        $insertArray = [];

        $header = $this->getLastConciliarHeader();
        // return $header;
        if ($header->status != ReconciliationHeader::OPEN_STATUS) {

            $error = \Illuminate\Validation\ValidationException::withMessages(
                [
                    'No existe una conciliación abierta, comuniquese con el administrador',
                ]
            );
            throw $error;
        }

        $file = storage_path($header->path);

        $itemsArray = [];

        for ($i = 0; $i < count($infoArray); $i++) {

            $account = Account::where('local_account', "=", $infoArray[$i]['local_account'])
                ->where('company_id', "=", $user->current_company)
                ->first();


            $item = [
                'header_id' => $header->id,
                'account_id' => $account->id,
                'debit_externo' => $infoArray[$i]['debitExternal'],
                'debit_local' => $infoArray[$i]['debitLocal'],
                'credit_externo' => $infoArray[$i]['creditExternal'],
                'credit_local' => $infoArray[$i]['creditLocal'],
                'balance_externo' => $infoArray[$i]['saldoExtracto'],
                'balance_local' => $infoArray[$i]['saldoContable'],
                'total' => $infoArray[$i]['total'],
                'status' => ReconciliationItem::CLOSE_STATUS,

            ];

            $itemsArray[] = $item;
            $item = [];
        }

        $file = Storage::files($header->file_path);

        $header->close_by = $user->id;
        $header->fecha_end = date("Y-m-d H:i:s");
        $header->status = ReconciliationHeader::CLOSE_STATUS;
        $header->fecha_cierre = $fechaCierre;

        $fromPath = storage_path($header->file_path);
        $toPath = storage_path('app/conciliaciones/' . $user->current_company . '/' . $header->file_name);

        $header->file_path = 'app/conciliaciones/' . $user->current_company . '/' . $header->file_name;
        $header->save();
        rename($fromPath, $toPath);
        $itemTable = new ReconciliationItem();
        $itemTable->setTable($this->conciliar_items_table);
        $itemTable->insert($itemsArray);


        $localInfo = $this->getIniInsertLocalArray(storage_path($header->file_path));
        $externalInfo = $this->getIniInsertExternalArray(storage_path($header->file_path));

        $items = $itemTable->where('header_id', '=', $header->id)
            ->with('account')
            ->get();


        for ($i = 0; $i < count($items); $i++) {

            for ($j = 0; $j < count($externalInfo); $j++) {

                if ($items[$i]->account->bank_account == $externalInfo[$j]['numero_cuenta']) {

                    $externalInfo[$j]['item_id'] = $items[$i]->id;
                }
            }

            for ($k = 0; $k < count($localInfo); $k++) {

                if ($items[$i]->account->local_account == $localInfo[$k]['local_account']) {

                    $localInfo[$k]['item_id'] = $items[$i]->id;
                }
            }
        }

        $localTable = new ReconciliationLocalValues($this->conciliar_local_values_table);
        $localTable->insert($localInfo);


        $externalTable = new ReconciliationExternalValues($this->conciliar_external_values_table);
        $externalTable->insert($externalInfo);
        return $header;
    }

    public function uploadAccountFile(Request $request)
    {
        if (!$request->hasFile('file')) {
            return $this->badRequestResponse("Param 'file' not found");
        }

        if (!$request->has('account_id')) {
            return $this->badRequestResponse("Param 'account_id' not found");
        }

        $accountId = $request->input('account_id');
        $account = Account::with('map')->find($accountId);

        $tmpItems = $this->uploadConciliationExternalService->processFile($this->user, $account, $request->file);

        return $this->showArray($tmpItems);
    }

    public function balanceCloseAccount(Request $request)
    {
        $validated = $request->validate([
            'externalBalance' => 'required|numeric|between:0,999999999999.99',
            'localBalance' => 'required|numeric|between:0,999999999999.99',
            'accountId' => 'required|exists:accounts,id',
        ]);

        return $this->reconciliationService->balanceCloseAccount(
            $validated['externalBalance'],
            $validated['localBalance'],
            $validated['accountId'],
            $this->user->current_company
        );
        return $request->externalBalance;
    }


    public function referencesFileUpload(Request $request)
    {
        $itemsTableName = $this->getReconciliationItemTableName($this->companyId);
        $itemsTable = (new ReconciliationItem())->setTable($itemsTableName)->where('process', $request->process)->first();
        if (!$itemsTable) {
            throw new Exception('No valid process', 500);
        }

        $request->validate([
            'files' => 'required'
        ]);

        return $this->reconciliationService->referencesFileUpload($request->files->all(), $request->process, $this->companyId);
        return $request->files->all();
    }




    //TODO: REMOVE UNUSED


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

    public function cellToInsertExterno($insertCell)
    {

        $insert =   [
            'tx_type_id' => '',
            'tx_type_name' => '',
            'item_id' => '',
            'descripcion' => $insertCell["TIPO DE TRANSACCION/DESCRIPCION"] == null ? '' : $insertCell["TIPO DE TRANSACCION/DESCRIPCION"],
            'operador' => '', // $insertCell["OPERADOR"],
            'valor_credito' => $insertCell["VALOR CRÉDITO"],
            'valor_debito' => $insertCell["VALOR DEBITO"],
            'valor_debito_credito' => $insertCell["VALOR (DEBITO/CREDITO)"],
            'fecha_movimiento' => $insertCell["FECHA DEL MOVIMIENTO"],
            'fecha_archivo' => $insertCell["FECHA DEL ARCHIVO"],
            'codigo_tx' => $insertCell["CODIGO DE TRANSACCION"],
            'referencia_1' => $insertCell["REFERENCIA 1"],
            'referencia_2' => $insertCell["REFERENCIA 2"],
            'referencia_3' => $insertCell["REFERENCIA 3"],
            'nombre_titular' => $insertCell["NOMBRE TITULAR"],
            'identificacion_titular' => $insertCell["IDENTIFICACION TITULAR"],
            'numero_cuenta' => $insertCell["NUMERO DE CUENTA"],
            'nombre_transaccion' => $insertCell["NOMBRE DE TRANSACCION"],
            'consecutivo_registro' => $insertCell["CONSECUTIVO DE REGISTROS"],
            'nombre_oficina' => $insertCell["NOMBRE OFICINA"],
            'codigo_oficina' => $insertCell["CODIGO OFICINA"],
            'canal' => $insertCell["CANAL"],
            'nombre_proveedor' => $insertCell["NOMBRE PROVEEDOR"],
            'id_proveedor' => $insertCell["IDENTIFICACION DE PROVEEDOR"],
            'banco_destino' => $insertCell["BANCO DESTINO"],
            'fecha_rechazo' => $insertCell["FECHA DE RECHAZO"],
            'motivo_rechazo' => $insertCell["MOTIVO DE RECHAZO"],
            'ciudad' => $insertCell["CIUDAD"],
            'tipo_cuenta' => $insertCell["TIPO DE CUENTA"],
            'numero_documento' => $insertCell["NUMERO DE DOCUMENTO"],

        ];

        return $insert;
    }


    public function uploadConciliarContable(Request $request)
    {


        $user = Auth::user();
        $company =  Company::find($user->current_company);

        if ($company->map_id == null) {

            return $this->errorResponse("No hay un formato asociado", 400);
        }

        $headers = new ReconciliationHeader($this->conciliar_headers_table);

        $openHeader = $headers->where('status', ReconciliationHeader::OPEN_STATUS)
            ->orderBy('id', 'desc')->first();


        return $this->uploadConciliarContableService->startUploadProcess(
            $request->file,
            $company->map_id,
            $this->conciliar_tmp_local_values_table,
            $this->conciliar_items_table,
            $user,
            $openHeader
        );

        return $this->showMessage(true);
    }

    public function uploadIniFile(Request $request)
    {

        $user = Auth::user();

        $headers = new ReconciliationHeader($this->conciliar_headers_table);

        $lastHeader = $headers->orderBy('id', 'desc')->first();

        $ext = $request->file->extension() == 'txt' ? 'csv' : $request->file->extension();

        $request->file->storeAs('tmpUpload', $user->id . 'iniFile.' . $ext, 'local');

        $file = storage_path('app/tmpUpload/' . $user->id . 'iniFile.' . $ext);

        if ($lastHeader != null) {

            $lastHeader->file_name = $request->file->getClientOriginalName();
            $lastHeader->save();
        } else {

            $header = new ReconciliationHeader($this->conciliar_headers_table);


            $header->insert(
                [
                    'fecha_ini' => date('Y-m-d H:i:s'),
                    'fecha_end' => date('Y-m-d H:i:s'),
                    'created_by' => $user->id,
                    'step' => 1,
                    'status' => ReconciliationHeader::OPEN_STATUS,
                    'type' => ReconciliationHeader::TYPE_INITIAL
                ]
            );
        }

        $externalSaldos = $this->getExternalIniSaldos($request->file);

        $localSaldos = $this->getLocalIniSaldos($request->file);

        return $this->showArray(array("external" => $externalSaldos, "local" => $localSaldos));
    }



    public function getCuentasToConciliar()
    {
        $conciliarHeaderTable = new ReconciliationHeader($this->conciliar_headers_table);
        $conciliarHeaderOpen = $conciliarHeaderTable->where('status', '=', ReconciliationHeader::OPEN_STATUS)
            ->orderBy('id', 'desc')
            ->first();

        $conciliarHeaderClose = $conciliarHeaderTable->where('status', '=', ReconciliationHeader::CLOSE_STATUS)
            ->orderBy('id', 'desc')
            ->first();


        $ReconciliationItemsTable = new ReconciliationItem($this->conciliar_items_table);

        $ReconciliationItemsClose = $ReconciliationItemsTable->where('header_id', '=', $conciliarHeaderClose->id)
            ->with(['account', 'account.companies', 'account.banks' => function ($q) {
                $q->orderBy('banks.name', 'desc');
            }])
            ->get();

        if ($conciliarHeaderOpen == NULL) {

            for ($j = 0; $j < $ReconciliationItemsClose->count(); $j++) {

                $ReconciliationItemsClose[$j]->ant_externo = $ReconciliationItemsClose[$j]->balance_externo;
                $ReconciliationItemsClose[$j]->ant_local = $ReconciliationItemsClose[$j]->balance_local;
            }

            return $this->showArray($ReconciliationItemsClose);
        } else {

            $ReconciliationItemsOpen = $ReconciliationItemsTable->where('header_id', '=', $conciliarHeaderOpen->id)
                ->with(['account', 'account.companies', 'account.banks' => function ($q) {
                    $q->orderBy('banks.name', 'desc');
                }])
                ->get();

            for ($i = 0; $i < $ReconciliationItemsOpen->count(); $i++) {

                for ($j = 0; $j < $ReconciliationItemsClose->count(); $j++) {

                    if ($ReconciliationItemsOpen[$i]->account_id == $ReconciliationItemsClose[$j]->account_id) {

                        $ReconciliationItemsOpen[$i]->ant_externo = $ReconciliationItemsClose[$j]->balance_externo;
                        $ReconciliationItemsOpen[$i]->ant_local = $ReconciliationItemsClose[$j]->balance_local;
                    }
                }
            }

            return $this->showArray($ReconciliationItemsOpen);
        }
    }

    public function setIniProcess(Request $request)
    {


        return Session::get('file');
    }




    public function isIniConciliar()
    {

        if (!Schema::hasTable($this->conciliar_headers_table)) {

            $this->createTablesInit();
        }

        $conciliarModel = new ReconciliationHeader($this->conciliar_headers_table);

        $conciliarTable = $conciliarModel->where('id', '=', 1)
            ->where('status', '=', ReconciliationHeader::CLOSE_STATUS)
            ->get();


        if (count($conciliarTable) == 0) {

            return $this->showMessage('', false);
        } else {

            return $this->showMessage('', true);
        }
    }

    private function fileExplode($file, $delimiter)
    {

        $rows = explode("\n", $file);

        $colTitles = explode($delimiter, $rows[0]);
        $colValues = explode($delimiter, $rows[1]);


        if (!(count($colTitles) > 1)) {

            return false;
        }

        for ($i = 0; $i < count($colTitles); $i++) {

            $cell[] = ["title" => $colTitles[$i], "value" => $colValues[$i], "type" => "txt"];
        }

        return $cell;
    }

    public function createTableConciliarHeaders()
    {

        Schema::create($this->conciliar_headers_table, function ($table) {
            $table->increments('id');
            $table->integer('created_by')->unsigned();
            $table->integer('close_by')->unsigned()->nullable();
            $table->date('fecha_ini');
            $table->date('fecha_end')->nullable();
            $table->date('fecha_cierre')->nullable();
            $table->date('ulitmo_cierre')->nullable();
            $table->string('status');
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['status'])->default('created');

            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('close_by')->references('id')->on('users');
        });
    }

    public function createTmpTableReconciliationItems()
    {

        Schema::create($this->conciliar_items_tmp_table, function ($table) {
            $table->increments('id');
            $table->integer('header_id')->unsigned();
            $table->integer('account_id')->unsigned();
            $table->decimal('debit_externo', 24, 2);
            $table->decimal('credit_externo', 24, 2);
            $table->decimal('debit_local', 24, 2);
            $table->decimal('credit_local', 24, 2);
            $table->decimal('balance_externo', 24, 2);
            $table->decimal('prev_externo', 24, 2)->default(0);
            $table->decimal('balance_local', 24, 2);
            $table->decimal('prev_local', 24, 2)->default(0);
            $table->decimal('total', 24, 2);
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('status')->default('created');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('accounts');
        });
    }

    public function createTableReconciliationItems()
    {

        Schema::create($this->conciliar_items_table, function ($table) {
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

            $table->foreign('header_id')->references('id')->on($this->conciliar_headers_table);
            $table->foreign('account_id')->references('id')->on('accounts');
        });
    }

    public function createTableReconciliationExternalValues()
    {

        Schema::create($this->conciliar_external_values_table, function ($table) {
            $table->bigIncrements('id');
            $table->boolean('matched')->default(false);
            $table->bigInteger('tx_type_id')->unsigned();
            $table->string('tx_type_name')->nullable();
            $table->integer('item_id')->unsigned();
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

            $table->foreign('tx_type_id')->references('id')->on('external_tx_types');
            $table->foreign('item_id')->references('id')->on($this->conciliar_items_table);
        });
    }

    public function createTmpTableReconciliationExternalValues()
    {

        Schema::dropIfExists($this->conciliar_tmp_external_values_table);

        Schema::create($this->conciliar_tmp_external_values_table, function ($table) {
            $table->bigIncrements('id');
            $table->boolean('matched')->default(false);
            $table->integer('tx_type_id')->unsigned()->nullable();
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

    public function createTableConciliarLocalValues()
    {

        Schema::create($this->conciliar_local_values_table, function ($table) {
            $table->bigIncrements('id');
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

            $table->softDeletes();
            $table->timestamps();

            $table->foreign('item_id')->references('id')->on($this->conciliar_items_table);
        });
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

    public function createTableConciliarLocalTxType()
    {


        Schema::dropIfExists($this->conciliar_local_tx_type);


        Schema::create($this->conciliar_local_tx_type, function ($table) {
            $table->bigIncrements('id');
            $table->string('description');
            $table->string('tx');
            $table->integer('company_id')->unsigned()->nullable();
            $table->string('reference');
            $table->string('sign');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies');
        });
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

    private function getLocalIniSaldos($file)
    {

        $user = Auth::user();

        $localInsert = $this->getIniInsertLocalArray($file);

        $this->createTmpTableConciliarLocalValues();

        DB::table($this->conciliar_tmp_local_values_table)->insert($localInsert);

        $query = DB::table($this->conciliar_tmp_local_values_table)
            ->select(DB::raw("SUM(valor_credito) as credit,SUM(valor_debito) as debit, " . $this->conciliar_tmp_local_values_table . ".local_account"))
            ->join('accounts', $this->conciliar_tmp_local_values_table . '.local_account', '=', 'accounts.local_account')
            ->join('banks', 'accounts.bank_id', '=', 'banks.id')
            ->where('accounts.company_id', '=', $user->current_company)
            ->groupBy('local_account')
            ->get();

        Schema::dropIfExists($this->conciliar_tmp_local_values_table);
        return $query;
    }

    private function getIniInsertLocalArray($file)
    {

        $user = Auth::user();
        $localInsert = null;

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
        $spreadsheet->setActiveSheetIndex(1);
        $worksheet = $spreadsheet->getActiveSheet();
        $highestRow = $worksheet->getHighestDataRow();
        $highestColumn = $worksheet->getHighestColumn();
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
        $externalInsert = [];


        for ($row = 3; $row <= $highestRow; $row++) {

            $cell = array();
            $insertCell = array();


            for ($col = 1; $col <= CONTABLE_COLS; $col++) {

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


            $tx_type_id = null;
            $tx_type_name = null;

            if ($insertCell[2] === null) {

                continue;
            }


            $tx_type_talbe_obj = new LocalTxType($this->conciliar_local_tx_type);
            $tx_type_table = $tx_type_talbe_obj->where('description', 'like', '%' . strtoupper($insertCell[8]) . '%')
                ->get();


            if (count($tx_type_table) != 0) {

                $tx_type_id = $tx_type_table[0]->id;
                $tx_type_name = $tx_type_table[0]->tx;
            }



            if ($insertCell[5] == null && $insertCell[6] == null && $insertCell[7] == null) {
            }


            $localInsert[] = [
                'tx_type_id' => $tx_type_id,
                'tx_type_name' => $tx_type_name,
                'local_account' => $insertCell[0],
                'cuenta_externa' => $insertCell[3],
                'fecha_movimiento' => date('Y-m-d', strtotime($insertCell[4])),
                'numero_comprobante' => $insertCell[5],
                'referencia_1' => $insertCell[6],
                'identificacion_tercero' => $insertCell[7],
                'descripcion' => $insertCell[8],
                'valor_debito' => $insertCell[9],
                'valor_credito' => $insertCell[10],
            ];
        }

        return $localInsert;
    }
    private function getIniInsertExternalArray($file)
    {

        $user = Auth::user();
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
        $spreadsheet->setActiveSheetIndex(0);
        $worksheet = $spreadsheet->getActiveSheet();
        $highestRow = $worksheet->getHighestRow();
        $highestColumn = $worksheet->getHighestColumn();
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
        $externalInsert = [];

        for ($row = 3; $row <= $highestRow; $row++) {

            $cell = array();
            $insertCell = array();


            for ($col = 1; $col < RECAUDO_COLS; $col++) {

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

            $tx_type_id = null;
            $tx_type_name = null;

            if ($insertCell[3] === null ||  $insertCell[3] == "") {

                continue;
            }


            $account = Account::where('bank_account', $insertCell[2])
                ->with('banks')
                ->first();

            if ($account == null) {

                $error = \Illuminate\Validation\ValidationException::withMessages(
                    [
                        'No existe una cuenta externa con número: ' . $insertCell[2],
                    ]
                );
                throw $error;
            }

            $tx_type_table = ExternalTxType::where('bank_id', $account->bank_id)
                ->where('description', '=', trim($insertCell[7]))
                ->get();

            $account = Account::where('bank_account', $insertCell[2])
                ->with('banks')
                ->first();



            if (count($tx_type_table) > 0) {

                $tx_type_id = $tx_type_table[0]->id;
                $tx_type_name = $tx_type_table[0]->tx;
            }

            $tx_type_table = ExternalTxType::where('type', 'COMPUESTO')->get();

            for ($i = 0; $i < count($tx_type_table); $i++) {

                if (strpos(strtoupper($insertCell[7]), $tx_type_table[$i]->description) !== false) {

                    $tx_type_id = $tx_type_table[$i]->id;
                    $tx_type_name = $tx_type_table[$i]->tx;
                    break;
                }
            }

            if ($tx_type_id === null) {


                $error = \Illuminate\Validation\ValidationException::withMessages(
                    [
                        'No existe una transacción con descripción: ' . strtoupper($insertCell[7])
                    ]
                );
                throw $error;
            }


            $externalInsert[] = [
                'tx_type_id' => $tx_type_id,
                'tx_type_name' => $tx_type_name,
                'numero_cuenta' => $insertCell[2],
                'fecha_movimiento' => date('Y-m-d', strtotime($insertCell[3])),
                'referencia_1' => $insertCell[4],
                'referencia_2' => $insertCell[5],
                'referencia_3' => $insertCell[6],
                'descripcion' => $insertCell[7],
                'valor_credito' => $insertCell[8],
                'valor_debito' => $insertCell[9],
            ];
        }

        return $externalInsert;
    }


    private function getExternalIniSaldos($file)
    {

        $user = Auth::user();

        $externalInsert = $this->getIniInsertExternalArray($file);

        $this->createTmpTableReconciliationExternalValues();

        DB::table($this->conciliar_tmp_external_values_table)->insert($externalInsert);

        $query = DB::table($this->conciliar_tmp_external_values_table)
            ->select(DB::raw("SUM(valor_credito) as credit,SUM(valor_debito) as debit, numero_cuenta,
                    banks.name, accounts.local_account, banks.name"))
            ->join('accounts', $this->conciliar_tmp_external_values_table . '.numero_cuenta', '=', 'accounts.bank_account')
            ->join('banks', 'accounts.bank_id', '=', 'banks.id')
            ->where('accounts.company_id', '=', $user->current_company)
            ->groupBy('numero_cuenta', 'banks.name', 'accounts.local_account', 'bank_account')
            ->orderBy('banks.name', 'DESC')
            ->orderBy('numero_cuenta', 'ASC')
            ->get();

        Schema::dropIfExists($this->conciliar_tmp_external_values_table);
        return $query;
    }
}


// set FOREIGN_key_checks = 0;
// drop TABLE conciliar_items_1;
// drop TABLE conciliar_items_2;
// drop TABLE conciliar_items_4;
// drop TABLE conciliar_headers_1;
// drop TABLE conciliar_headers_2;
// drop TABLE conciliar_headers_4;
// drop TABLE conciliar_local_values_1;
// drop TABLE conciliar_local_values_2;
// drop TABLE conciliar_local_values_4;
// drop TABLE conciliar_external_values_1;
// drop TABLE conciliar_external_values_2; 
// drop TABLE conciliar_external_values_4;
