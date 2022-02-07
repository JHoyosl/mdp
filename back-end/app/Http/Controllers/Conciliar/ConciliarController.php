<?php

namespace App\Http\Controllers\Conciliar;

ini_set('memory_limit', '-1'); //TODO: Valor para producción
ini_set('max_execution_time', '300');

use App\Models\Account;
use App\Models\Company;
use App\Models\ConciliarExternalValues;
use App\Models\ConciliarHeader;
use App\Models\ConciliarItem;
use App\Models\ConciliarLocalValues;
use App\Models\ExternalTxType;
use App\Http\Controllers\ApiController;
use App\Models\LocalTxType;
use App\Models\MapFile;
use App\Models\User;
use Excel;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Schema;


define("CONTABLE_COLS", 36);
define("RECAUDO_COLS", 13);

class ConciliarController extends ApiController
{
    protected $conciliar_headers_table = '';
    protected $conciliar_items_table = '';
    protected $conciliar_items_tmp_table = '';
    protected $conciliar_external_values_table = '';
    protected $conciliar_tmp_external_values_table = '';
    protected $conciliar_local_values_table = '';
    protected $c = '';
    protected $conciliar_local_tx_type = '';
    protected $conciliar_external_tx_type = 'external_tx_types';
    

    public function __construct(){

        $this->middleware(function ($request, $next) {

            $user = Auth::user();

            $this->init($user);

            return $next($request);
        });

        
        $this->middleware('auth:api')->only(
                ['index',
                 'uploadIniFile',
                 'setIniProcess',
                 'isIniConciliar',
                 'closeIniConciliar',
                 'uploadAccountFile',
                 'getCuentasToConciliar',
                 'uploadConciliarContable'
                ]);



    }

    function init($user){

        $this->conciliar_headers_table = 'conciliar_headers_'.$user->current_company;
        $this->conciliar_items_table = 'conciliar_items_'.$user->current_company;
        $this->conciliar_items_tmp_table = 'conciliar_tmp_items_'.$user->current_company;
        $this->conciliar_external_values_table = 'conciliar_external_values_'.$user->current_company;
        $this->conciliar_tmp_external_values_table = 'conciliar_tmp_external_values_'.$user->current_company;
        $this->conciliar_local_values_table = 'conciliar_local_values_'.$user->current_company;
        $this->conciliar_tmp_local_values_table = 'conciliar_tmp_local_values_table_'.$user->current_company;
        $this->conciliar_local_tx_type = 'conciliar_local_tx_type_'.$user->current_company;
        $this->conciliar_external_tx_type = 'external_tx_types_'.$user->current_company;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {   
        

        $user = Auth::user();

        $headers = new ConciliarHeader($this->conciliar_headers_table);
        
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

    }

    public function createTablesInit(){
        // DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        // Schema::dropIfExists($this->conciliar_headers_table);
        // $this->createTableConciliarHeaders();
        // DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->createTableConciliarHeaders();
        $this->createTableConciliarLocalTxType();
        $this->createTableConciliarItems();
        $this->createTableConciliarExternalValues();
        $this->createTableConciliarLocalValues();

        return true;
    }

    private function getLastConciliarHeader(){


        $conciliarModel = new ConciliarHeader($this->conciliar_headers_table);

        $conciliarTable = $conciliarModel->where('status','=',ConciliarHeader::OPEN_STATUS)
                ->first();

        return $conciliarTable;


    }

    private function truncateTmpTables(){

        $tmpExternalValues = new ConciliarExternalValues();
        $tmpExternalValues->truncate();

        $tmpLocalValues = new ConciliarLocalValues();
        $tmpLocalValues->truncate();

    }

    public function closeIniConciliar(Request $request){

         
        $user = Auth::user();

        if(!is_dir(storage_path('app/conciliaciones/'.$user->current_company.'/'))) {

            mkdir(storage_path('app/conciliaciones/'.$user->current_company.'/'), 0775);
        
        }

        

        $info = $request->all();


        $infoArray = json_decode($info['info'],true);
        $fechaCierre = $info['fecha_cierre'];

        $insertArray = [];


        $header = $this->getLastConciliarHeader();
        // return $header;
        if($header->status != ConciliarHeader::OPEN_STATUS){

            $error = \Illuminate\Validation\ValidationException::withMessages(
                        ['No existe una conciliación abierta, comuniquese con el administrador',
            ]);
            throw $error;
        }

        $file = storage_path($header->path);

        $itemsArray = [];

        for($i = 0; $i < count($infoArray); $i++){

            $account = Account::where('local_account',"=",$infoArray[$i]['local_account'])
                            ->where('company_id',"=",$user->current_company)
                            ->first();


            $item = [
                'header_id'=>$header->id, 
                'account_id'=>$account->id,
                'debit_externo'=>$infoArray[$i]['debitExternal'], 
                'debit_local'=>$infoArray[$i]['debitLocal'],
                'credit_externo'=>$infoArray[$i]['creditExternal'], 
                'credit_local'=>$infoArray[$i]['creditLocal'],
                'balance_externo'=>$infoArray[$i]['saldoExtracto'],
                'balance_local'=>$infoArray[$i]['saldoContable'],
                'total'=>$infoArray[$i]['total'],
                'status'=>ConciliarItem::CLOSE_STATUS,

            ];

            $itemsArray[] = $item;
            $item = [];
        }


        $file = Storage::files($header->file_path);

        $header->close_by = $user->id;
        $header->fecha_end = date("Y-m-d H:i:s");
        $header->status = ConciliarHeader::CLOSE_STATUS;
        $header->fecha_cierre = $fechaCierre;

        $fromPath = storage_path($header->file_path);
        $toPath = storage_path('app/conciliaciones/'.$user->current_company.'/'.$header->file_name);
        
        $header->file_path = 'app/conciliaciones/'.$user->current_company.'/'.$header->file_name;
        $header->save();
        rename($fromPath, $toPath);
        $itemTable = new ConciliarItem($this->conciliar_items_table);
        $itemTable->insert($itemsArray);


        $localInfo = $this->getIniInsertLocalArray(storage_path($header->file_path));
        $externalInfo = $this->getIniInsertExternalArray(storage_path($header->file_path));

        $items = $itemTable->where('header_id','=',$header->id)
                ->with('account')
                ->get();

        
        for($i = 0; $i < count($items); $i++){

            for($j = 0; $j < count($externalInfo); $j++){

                if($items[$i]->account->bank_account == $externalInfo[$j]['numero_cuenta']){

                    $externalInfo[$j]['item_id'] = $items[$i]->id;
                }

            }

            for($k = 0; $k < count($localInfo); $k++){

                if($items[$i]->account->local_account == $localInfo[$k]['local_account']){

                    $localInfo[$k]['item_id'] = $items[$i]->id;

                }
            }

        }
        
        $localTable = new ConciliarLocalValues($this->conciliar_local_values_table);
        $localTable->insert($localInfo);

        
        $externalTable = new ConciliarExternalValues($this->conciliar_external_values_table);
        $externalTable->insert($externalInfo);
        return $header;
    }



    private function getCurrentConciliacion(){ // se instancia la tabla de cabecera y se trae el registro con estado abierto

        $user = Auth::user();

        $conciliarHeaderTable = new ConciliarHeader($this->conciliar_headers_table);
        $header = $conciliarHeaderTable->where('status',ConciliarHeader::OPEN_STATUS)
                            ->orderBy('id', 'desc')
                            ->first();

        if($header == null){ //si no existe uno con estado abierto se instancia la tabla y se inserta un registro y se devuelve este registro
 
            $header = new ConciliarHeader($this->conciliar_headers_table);


            $header->insert(
                    [
                        'fecha_ini' => date('Y-m-d H:i:s'),  
                        'created_by' => $user->id,
                        'status' => ConciliarHeader::OPEN_STATUS,
                        
                    ]
                );

            
        }

        return $header;

    }

    public function uploadAccountFile(Request $request)
    {
        $user = Auth::user(); 

        $data = $request->all(); 
       
        $actualHeader = $this->getCurrentConciliacion(); //Trae el header de la conciliación actual abierta

        $itemTable = new ConciliarItem($this->conciliar_items_table); //inicializa o instancia la tabla de conciliar items



        $currentItem = $itemTable->where('header_id','=',$actualHeader->id) //Trae el registro de la tabla items_conciliar que esté relacionado con la cabecera y con la cuenta enviada en el request
                        ->where('account_id','=',$data['account_id'])
                        ->first();

        // if($currentItem === NULL){
            
        // }

        $ext = $request->file->extension()=='txt'?'csv':$request->file->extension(); //devuelve la extension del archivo
        
        $fileName = $user->current_company.'_'.$actualHeader->id.'_'.$data['account_id'].'_accountFile.'.$ext; //se construye el nombre del archivo con el id de la compañia actual, el id de la cabecera, el id de la cuenta y la extensión

        $request->file->storeAs('tmpUpload',$fileName,'local'); //Almacena el archivo temporalmente. Si no desea que se asigne automáticamente un nombre de archivo a su archivo almacenado, puede usar el storeAsmétodo, que recibe la ruta, el nombre de archivo y el disco (opcional) como argumentos


        $filePath = 'app/tmpUpload/'.$fileName; //ruta de archivo

        if($currentItem == null){ //si no hay registros en la tabla de items conciliar se inicializa la tabla, se inserta $itemInfo y se trae el item actual acabado de insertar

            $item = new ConciliarItem($this->conciliar_items_table);

            $itemInfo = [
                    'header_id'=> $actualHeader->id, 
                    'account_id'=> $data['account_id'],
                    'debit_externo'=>0, 
                    'debit_local'=>0,
                    'credit_externo'=>0, 
                    'credit_local'=>0,
                    'balance_externo'=>0,
                    'balance_local'=>0,
                    'file_path'=>$filePath,
                    'file_name'=> $request->file->getClientOriginalName(), //Recupera el nombre original de un archivo cargado
                    'total'=>0,
                    'status'=>ConciliarHeader::OPEN_STATUS,
                ];
            $item->insert($itemInfo);

            $currentItem = $itemTable->where('header_id','=',$actualHeader->id)
                        ->where('account_id','=',$data['account_id'])
                        ->first();
            

        }

        $account = Account::with('map')->find($data['account_id']); // Devuelve el objeto de la cuenta con el mapeo(contable) relacionado con la cuenta
        return $this->mapUploadFileAccount($request->file, $account, $currentItem);
        $mapInfo = $this->mapUploadFileAccount($request->file, $account, $currentItem); //salto a la línea 431
        // dd($mapInfo);
        $currentItem->credit_externo = $mapInfo->credit_externo;
        $currentItem->debit_externo = $mapInfo->debit_externo;

        $currentItem->save();
        
        return $this->showArray($mapInfo);
    }

    private function getInfoMaped(){


    }

    private function mapUploadFileAccount($file, $account, $item)
    {
        $user = Auth::user();

        if(!Schema::hasTable($this->conciliar_items_tmp_table)){ //si no existe en bd la tabla temporal de items conciliar se crea

            $this->createTmpTableConciliarItems();

        }
        if(!Schema::hasTable($this->conciliar_tmp_external_values_table)){ //si no existe en bd la tabla temporal de valores conciliar se crea

            $this->createTmpTableConciliarExternalValues();

        }else{// de lo contrario 

            
            $header = $this->getCurrentConciliacion(); //se instancia la tabla de cabecera y se trae el registro con estado abierto, si no existe uno con estado abierto se instancia la tabla y se inserta un registro y se devuelve este registro

            $tmpItemsTable = new ConciliarItem($this->conciliar_items_tmp_table); //se instancia la tabla temporal de items

            $tmpItems = $tmpItemsTable->where('header_id', $header->id)//se busca si en esta tabla hay creado un registro con el id del $header y la cuenta que se está conciliando
                            ->where('account_id',$account->id)
                            ->first();
            
            if($tmpItems == null){ //si no existe este regitro se crea y se inserta
                
                $item = [
                    'header_id'=>$header->id, 
                    'account_id'=>$account->id,
                    'debit_externo'=>0, 
                    'debit_local'=>0,
                    'credit_externo'=>0, 
                    'credit_local'=>0,
                    'balance_externo'=>0,
                    'balance_local'=>0,
                    'total'=>0,
                    'status'=>ConciliarItem::OPEN_STATUS,

                ];
                $tmpItemsTable = new ConciliarItem($this->conciliar_items_tmp_table);

                $tmpItemsTable->insert($item);

                $tmpItems = $tmpItemsTable->where('header_id', $header->id)//se trae el dato insertado
                            ->where('account_id',$account->id)
                            ->first();
                // return $tmpItems;

            }   
        }   
            
        $externalTxTable = ExternalTxType::where('bank_id',$account->bank_id)->get(); //Devuelve todas las transacciones del banco relacionado con la cuenta
        $mapFile = MapFile::find($account->map_id);//devuelve el mapeo del archivo del banco
        // dd($mapFile);

        $map =  json_decode($mapFile->map, true); //mapeo del como array
        $base =  json_decode($mapFile->base);
        // dd($map);
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);//Carga un libro de trabajo desde un archivo
        $spreadsheet->setActiveSheetIndex(0);//establecer índice de hoja activa
        $worksheet = $spreadsheet->getActiveSheet();//obtener hoja activa
        $highestRow = $worksheet->getHighestRow(); //obtener la fila más alta
        $highestColumn = $worksheet->getHighestColumn();//obtener la columna más alta
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);//índice de columna más alto
        $externalInsert = [];

        $startRow = 1;
        
        switch ($account->id) {
            case 8:
                    $startRow= 2;
                break;
            default:
                # code...
                break;
        }

        // return $highestRow;
        for($row = 2; $row <= $highestRow; $row++){//recorre la hoja por filas hasta el índice de fila más alto
            
            $cell = array();
            $insertCell = array();

            $accountMap = DB::Table('map_bank_index')//devuelve todos los campos del mapeo de un banco
                    ->orderBy('id')
                    ->get();

            // return array($map,$accountMap);
            for($j = 0; $j < count($accountMap); $j++){ //recorre todos los campos del mapeo de un banco

                $find = false;
                for($i = 0; $i < count($map); $i++){ //recorre todos los campos del mapeo de la cuenta externa
                    $mapIndex = $map[$i]['mapIndex']; //imprime el mapindex en la posición i del mapeo de la cuenta
                    $fileColumn = $map[$i]['fileColumn']+1;

                    if($map[$i]['mapIndex'] == $accountMap[$j]->id && $map[$i]['mapIndex'] != 0){
                        $typeCell = $worksheet->getCellByColumnAndRow($fileColumn, $row)->getDataType();

                        switch($typeCell){
                            
                            case "null":
                                $valueCell = null;
                            break;
                            case "s":
                                $valueCell = trim($worksheet->getCellByColumnAndRow($fileColumn, $row)->getValue());
                            break;
                            case "f":
                                $valueCell = $worksheet->getCellByColumnAndRow($fileColumn, $row)->getCalculatedValue();
                            break;
                            
                            case "n":
                                
                                $tmpValueCell = $valueCell = $worksheet->getCellByColumnAndRow($fileColumn, $row)->getValue();
                                //validar si es de tipo fecha
                                if( \PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($valueCell = $worksheet->getCellByColumnAndRow($fileColumn, $row))){
                                    
                                    $tmpValueCell = date("Y-m-d H:i:s",\PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($tmpValueCell));
                                    
                                }
                                
                                $valueCell = $tmpValueCell;
                                
                            break;
                            
                            default:
                                $valueCell = $worksheet->getCellByColumnAndRow($fileColumn, $row)->getValue();
                            break;
                        }
                        $insertCell[$accountMap[$j]->description] = $valueCell;
                        $find = true;
                    }
                }

                if(!$find){

                    $insertCell[$accountMap[$j]->description] = null;
                }

            }

            if($insertCell['FECHA DEL MOVIMIENTO'] == null){

                continue;
            }
            // dd($insertCell);

            $tmpInsertCell = $this->cellToInsertExterno($insertCell);
            
            $tmpInsertCell['item_id'] = $tmpItems['id'];
            
            $txInfo = $this->getTxInfo($tmpInsertCell, $account->bank_id);

            if($txInfo[0]){

                $tmpInsertCell['tx_type_id'] = $txInfo[1]['id'];
                $tmpInsertCell['tx_type_name'] = $txInfo[1]['tx'];
                
            }else{

                $error = \Illuminate\Validation\ValidationException::withMessages(
                ['No existe una transacción con descripción: '.$tmpInsertCell['descripcion'],$tmpInsertCell,$txInfo]
                );
                throw $error;
            }
            


            $insertArray[] = $tmpInsertCell;
            // dd($insertArray);
        }
        // dd($insertArray);
        $externalValuesTmp = new ConciliarItem($this->conciliar_tmp_external_values_table);

    
        $externalValuesTmp->where('item_id',$tmpItems->id)->delete();
        $externalValuesTmp->insert($insertArray) ;
        
        $query = DB::table($this->conciliar_tmp_external_values_table)
                ->select(DB::raw("SUM(valor_credito) as credit,SUM(valor_debito) as debit,item_id"))
                ->where('item_id',$tmpItems->id)
                ->groupBy('item_id')
                ->get();
        // return $query;
        if($query[0]->credit == null || $query[0]->debit == null)
        {
            $credit = DB::table($this->conciliar_tmp_external_values_table)
                    ->where('item_id',$tmpItems->id)
                    ->where('valor_debito_credito', '<', 0 )
                    ->select(DB::raw("SUM(valor_debito_credito * -1) as credit,item_id"))
                    ->groupBy('item_id')
                    ->get();
            
            $debit = DB::table($this->conciliar_tmp_external_values_table)
                ->where('item_id',$tmpItems->id)
                ->where('valor_debito_credito', '>', 0 )
                ->select(DB::raw("SUM(valor_debito_credito) as debit,item_id"))
                ->groupBy('item_id')
                ->get();

            $tmpItems->credit_externo = $credit[0]->credit;
            $tmpItems->debit_externo = $debit[0]->debit;
        
            $tmpItems->save();
        }else{
            $tmpItems->credit_externo = $query[0]->credit;
            $tmpItems->debit_externo = $query[0]->debit;

            $tmpItems->save();
        }
        return $tmpItems;
        

    }

    public function getTxInfo($values, $bank_id){

        $externalTxTable = ExternalTxType::where('bank_id',$bank_id)
                            ->where('reference','like', '%'.$values['codigo_tx'].'%')
                            ->get();
        
        if(is_numeric($values['codigo_tx'])){
            
            for($j = 0; $j < count($externalTxTable); $j++){

                if(intval($externalTxTable[$j]['reference']) == intval($values['codigo_tx'])){

                    return [true,$externalTxTable[0]];
                }else{

                    if($externalTxTable[$j]['reference'] == $values['codigo_tx']){

                        return [true,$externalTxTable[0]];
                    }
                }
            }
            
        }

        $externalTxTable = ExternalTxType::where('bank_id',$bank_id)
                            ->where('reference','like', '%'.$values['codigo_tx'].'%')
                            ->get();
        

        if(count($externalTxTable) > 0){

            return [true,$externalTxTable[0]];
        }

        $externalTxTable = ExternalTxType::where('bank_id',$bank_id)
                            ->where('description','like', '%'.$values['descripcion'].'%')
                            ->get();



        if(count($externalTxTable) > 0){

            return [true,$externalTxTable[0]];
        }else{

            return [false,$externalTxTable];
        }
    }

    public function cellToInsertExterno($insertCell){

        $insert =   [
            'matched' => false,
            'tx_type_id' => '',  
            'tx_type_name' => '',  
            'item_id' => '',  
            'descripcion' => $insertCell["TIPO DE TRANSACCION/DESCRIPCION"]==null?'':$insertCell["TIPO DE TRANSACCION/DESCRIPCION"],
            'operador' => $insertCell["OPERADOR"],
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
            'numero_documento' => $insertCell["NÚMERO DE DOCUMENTO"], 
            
        ];

        return $insert;
    }


    public function uploadConciliarContable(Request $request){


        $user = Auth::user();
        $company =  Company::find($user->current_company);

        if($company->map_id == null){

            return $this->errorResponse("No hay un formato asociado",400);
        }
        
        $headers = new ConciliarHeader($this->conciliar_headers_table);

        $openHeader = $headers->where('status',ConciliarHeader::OPEN_STATUS)
                                ->orderBy('id','desc')->first();
        
        $mapped = $this->getInserConciliarLocal($request->file, $company->map_id);
        // dd($mapped);
        $this->createTmpTableConciliarLocalValues();

        foreach (array_chunk($mapped,1000) as $rows)  
        {
            DB::table($this->conciliar_tmp_local_values_table)->insert($rows); //TODO: por rendimiento se parte el array, revisar optimización
        }
        $conciliarCuadre = DB::table($this->conciliar_tmp_local_values_table)
                ->select(
                        DB::raw("SUM(valor_credito) as credit,SUM(valor_debito) as debit, 
                            ".$this->conciliar_tmp_local_values_table.".local_account, accounts.id")
                        )
                ->join('accounts', $this->conciliar_tmp_local_values_table.'.local_account','=','accounts.local_account')
                ->join('banks', 'accounts.bank_id','=','banks.id')
                ->where('accounts.company_id','=',$user->current_company)
                ->groupBy('local_account','accounts.id')
                ->get();

        

        Schema::dropIfExists($this->conciliar_tmp_local_values_table);

        $itemTable = new ConciliarItem($this->conciliar_items_table);


        for($i = 0; $i < count($conciliarCuadre); $i++){

            $openItemTable = $itemTable->where('header_id','=',$openHeader->id)
                        ->where('account_id','=',$conciliarCuadre[$i]->id)
                        ->first();   

            if($openItemTable){

                $openItemTable->debit_local = (float)$conciliarCuadre[$i]->debit;
                $openItemTable->credit_local = (float)$conciliarCuadre[$i]->credit;

                $openItemTable->save();
            }else{

                $itemInfo = [
                    'header_id'=> $openHeader->id, 
                    'account_id'=> $conciliarCuadre[$i]->id,
                    'debit_externo'=>0, 
                    'debit_local'=>$conciliarCuadre[$i]->debit,
                    'credit_externo'=>0, 
                    'credit_local'=>$conciliarCuadre[$i]->credit,
                    'balance_externo'=>0,
                    'balance_local'=>0,
                    'file_path'=>'',
                    'file_name'=> '',
                    'total'=>0,
                    'status'=>ConciliarHeader::OPEN_STATUS,
                ];

                $item->insert($itemInfo);
            }

        }

        return $this->showMessage(true);

    }   

    public function uploadIniFile(Request $request){

        $user = Auth::user();

        $headers = new ConciliarHeader($this->conciliar_headers_table);

        $lastHeader = $headers->orderBy('id','desc')->first();

        $ext = $request->file->extension()=='txt'?'csv':$request->file->extension();

        $request->file->storeAs('tmpUpload',$user->id.'iniFile.'.$ext,'local');

        $file = storage_path('app/tmpUpload/'.$user->id.'iniFile.'.$ext);

        if($lastHeader != null){

            $lastHeader->file_name = $request->file->getClientOriginalName();
            $lastHeader->save();

        }else{

            $header = new ConciliarHeader($this->conciliar_headers_table);


            $header->insert(
                    [
                        'fecha_ini' => date('Y-m-d H:i:s'),  
                        'created_by' => $user->id,
                        'status' => ConciliarHeader::OPEN_STATUS,
                        'file_name' => $request->file->getClientOriginalName(),
                        'file_path' => 'app/tmpUpload/'.$user->id.'iniFile.'.$ext,
                    ]
            );

        }

        $externalSaldos = $this->getExternalIniSaldos($request->file);

        $localSaldos = $this->getLocalIniSaldos($request->file);

        return $this->showArray(array("external"=>$externalSaldos, "local"=>$localSaldos));
    }



    public function getCuentasToConciliar(){

        $conciliarHeaderTable = new ConciliarHeader($this->conciliar_headers_table);
        $conciliarHeaderOpen = $conciliarHeaderTable->where('status','=',ConciliarHeader::OPEN_STATUS)
                            ->orderBy('id', 'desc')
                            ->first();

        $conciliarHeaderClose = $conciliarHeaderTable->where('status','=',ConciliarHeader::CLOSE_STATUS)
                            ->orderBy('id', 'desc')
                            ->first();

        
        $conciliarItemsTable = new ConciliarItem($this->conciliar_items_table);

        $conciliarItemsClose = $conciliarItemsTable->where('header_id','=',$conciliarHeaderClose->id)
                            ->with(['account','account.companies','account.banks' => function($q){
                                             $q->orderBy('banks.name', 'desc');
                                         }])
                            ->get();

        if($conciliarHeaderOpen == NULL){

            

            for($j = 0; $j < $conciliarItemsClose->count(); $j++){

                $conciliarItemsClose[$j]->ant_externo = $conciliarItemsClose[$j]->balance_externo;
                $conciliarItemsClose[$j]->ant_local = $conciliarItemsClose[$j]->balance_local;
            }

            return $this->showArray($conciliarItemsClose);

        }else{

            $conciliarItemsOpen = $conciliarItemsTable->where('header_id','=',$conciliarHeaderOpen->id)
                            ->with(['account','account.companies','account.banks' => function($q){
                                             $q->orderBy('banks.name', 'desc');
                                         }])
                            ->get();

            for($i = 0; $i < $conciliarItemsOpen->count(); $i++){

                for($j = 0; $j < $conciliarItemsClose->count(); $j++){

                    if($conciliarItemsOpen[$i]->account_id == $conciliarItemsClose[$j]->account_id){

                        $conciliarItemsOpen[$i]->ant_externo = $conciliarItemsClose[$j]->balance_externo;
                        $conciliarItemsOpen[$i]->ant_local = $conciliarItemsClose[$j]->balance_local;
                    }
                }
            }

            return $this->showArray($conciliarItemsOpen);

        }
                            
    }

    public function setIniProcess(Request $request){


        return Session::get('file');
    }

    


    public function isIniConciliar(){

        if(!Schema::hasTable($this->conciliar_headers_table)){

            $this->createTablesInit();
        }

        $conciliarModel = new ConciliarHeader($this->conciliar_headers_table);

        $conciliarTable = $conciliarModel->where('id','=',1)
                ->where('status','=',ConciliarHeader::CLOSE_STATUS)
                ->get();


        if(count($conciliarTable) == 0){

            return $this->showMessage('',false);
        }else{

            return $this->showMessage('',true);
        }
        
    }

    private function fileExplode($file, $delimiter){

        $rows = explode("\n",$file);
        
        $colTitles = explode($delimiter,$rows[0]);
        $colValues = explode($delimiter,$rows[1]);
        
        
        if(!(count($colTitles) > 1)){
            
            return false;
        }
        
        for($i = 0; $i < count($colTitles); $i++){
            
            $cell[] = ["title"=>$colTitles[$i],"value"=>$colValues[$i],"type"=>"txt"];  
            
        }
        
        return $cell;

    } 

    public function createTableConciliarHeaders(){

        Schema::create($this->conciliar_headers_table, function($table) {
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

    public function createTmpTableConciliarItems(){

        Schema::create($this->conciliar_items_tmp_table, function($table) {
            $table->increments('id');
            $table->integer('header_id')->unsigned();
            $table->integer('account_id')->unsigned();
            $table->decimal('debit_externo',24,2)->nullable();
            $table->decimal('credit_externo',24,2)->nullable();
            $table->decimal('debit_local',24,2);
            $table->decimal('credit_local',24,2);
            $table->decimal('balance_externo',24,2);
            $table->decimal('balance_local',24,2);
            $table->decimal('total',24,2);
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('status')->default('created');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('accounts');
            
        });
    }

    public function createTableConciliarItems(){

        Schema::create($this->conciliar_items_table, function($table) {
            $table->increments('id');
            $table->integer('header_id')->unsigned();
            $table->integer('account_id')->unsigned();
            $table->decimal('debit_externo',24,2);
            $table->decimal('credit_externo',24,2);
            $table->decimal('debit_local',24,2);
            $table->decimal('credit_local',24,2);
            $table->decimal('balance_externo',24,2);
            $table->decimal('balance_local',24,2);
            $table->decimal('total',24,2);
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('status')->default('created');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('header_id')->references('id')->on($this->conciliar_headers_table);
            $table->foreign('account_id')->references('id')->on('accounts');
            
        });
    }

    public function createTableConciliarExternalValues(){

        Schema::create($this->conciliar_external_values_table, function($table)  {
            $table->bigIncrements('id');
            $table->boolean('matched')->default(false);
            $table->bigInteger('tx_type_id')->unsigned();
            $table->string('tx_type_name')->nullable();
            $table->integer('item_id')->unsigned();
            $table->string('descripcion')->comment = 'transaccion/descripcion';
            $table->string('operador')->nullable();
            $table->decimal('valor_credito',24,2)->nullable();
            $table->decimal('valor_debito',24,2)->nullable();
            $table->decimal('valor_debito_credito',24,2)->nullable();
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

    public function createTmpTableConciliarExternalValues(){

        Schema::dropIfExists($this->conciliar_tmp_external_values_table);

        Schema::create($this->conciliar_tmp_external_values_table, function($table)  {
            $table->bigIncrements('id');
            $table->boolean('matched')->default(false);
            $table->integer('tx_type_id')->unsigned();
            $table->string('tx_type_name')->nullable();
            $table->integer('item_id')->unsigned()->nullable();
            $table->string('descripcion')->comment = 'transaccion/descripcion';
            $table->string('operador')->nullable();
            $table->decimal('valor_credito',24,2)->nullable();
            $table->decimal('valor_debito',24,2)->nullable();
            $table->decimal('valor_debito_credito',24,2)->nullable();
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

    public function createTableConciliarLocalValues(){

        Schema::create($this->conciliar_local_values_table, function($table)  {
            $table->bigIncrements('id');
            $table->boolean('matched')->default(false);
            $table->integer('item_id')->unsigned();
            $table->integer('tx_type_id')->unsigned()->nullable();
            $table->string('tx_type_name')->nullable();
            $table->string('cuenta_externa');
            $table->dateTime('fecha_movimiento');
            $table->string('descripcion')->comment = 'transaccion/descripcion';
            $table->string('referencia_1')->nullable();
            $table->string('referencia_2')->nullable();
            $table->string('referencia_3')->nullable();
            $table->string('otra_referencia')->nullable();
            $table->decimal('saldo_actual',24,2)->nullable();
            $table->decimal('valor_debito',24,2)->nullable();
            $table->decimal('saldo_anterior',24,2)->nullable();
            $table->decimal('valor_credito',24,2)->nullable();
            $table->decimal('valor_debito_credito',24,2)->nullable();
            $table->string('codigo_usuario')->nullable();
            $table->string('nombre_agencia')->nullable();
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
            $table->string('local_account');
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

    public function createTmpTableConciliarLocalValues(){

        Schema::dropIfExists($this->conciliar_tmp_local_values_table);

        Schema::create($this->conciliar_tmp_local_values_table, function($table)  {
            $table->bigIncrements('id');
            $table->boolean('matched')->default(false);
            $table->integer('item_id')->unsigned()->nullable();
            $table->integer('tx_type_id')->unsigned()->nullable();
            $table->string('tx_type_name')->nullable();
            $table->string('cuenta_externa')->nullable();
            $table->dateTime('fecha_movimiento');
            $table->string('descripcion')->comment = 'transaccion/descripcion';
            $table->string('referencia_1')->nullable();
            $table->string('referencia_2')->nullable();
            $table->string('referencia_3')->nullable();
            $table->string('otra_referencia')->nullable();
            $table->decimal('saldo_actual',24,2)->nullable();
            $table->decimal('valor_debito',24,2)->nullable();
            $table->decimal('saldo_anterior',24,2)->nullable();
            $table->decimal('valor_credito',24,2)->nullable();
            $table->decimal('valor_debito_credito',24,2)->nullable();
            $table->string('codigo_usuario')->nullable();
            $table->string('nombre_agencia')->nullable();
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
            $table->string('local_account')->nullable();
            $table->string('numero_lote')->nullable();
            $table->string('consecutivo_lote')->nullable();
            $table->string('tipo_registro')->nullable();
            $table->string('ambiente_origen')->nullable();
            $table->string('beneficiario')->nullable();
          
            $table->softDeletes();
            $table->timestamps();


        });

    }

    public function createTableConciliarLocalTxType(){


        Schema::dropIfExists($this->conciliar_local_tx_type);


        Schema::create($this->conciliar_local_tx_type, function($table)  {
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

    

    private function fileToArray($file){


        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
        $spreadsheet->setActiveSheetIndex(0);
        $worksheet = $spreadsheet->getActiveSheet();
        $highestRow = $worksheet->getHighestDataRow();
        $highestColumn = $worksheet->getHighestColumn();
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
        $externalInsert = [];

        for($row = 2; $row <= $highestRow; $row++){
            
            $cell = array();
            $insertCell = array();


            for($col = 1; $col <= CONTABLE_COLS; $col++){
                
                $typeCell = $worksheet->getCellByColumnAndRow($col, $row)->getDataType();

                switch($typeCell){
                    
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
                        if( \PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($valueCell = $worksheet->getCellByColumnAndRow($col, $row))){
                            
                            $tmpValueCell = date("Y-m-d H:i:s",\PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($tmpValueCell));
                            
                        }
                        
                        $valueCell = $tmpValueCell;
                        
                    break;
                    
                    default:
                        $tmpValueCell = $valueCell = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
                    break;
                }


                $cell[] = ["value"=>$valueCell,"type"=>$typeCell];
                    
                $insertCell[] = ($valueCell===null)?null:$valueCell;
                
            }

            $localInsert[] = $insertCell;
        }

        return $localInsert;
    }

    private function getInserConciliarLocal($file, $map_id){

        
        $fileArray = $this->fileToArray($file);
        // dd($fileArray);
        $mapModel = MapFile::find($map_id);

        $map = json_decode($mapModel->map, true);
        // dd($map[0]);
        // return $map;
        $tmpArray = array();
        for($i = 0; $i < CONTABLE_COLS; $i++){ //TODO: Volver el número de campos dinámico
            $found = false;
            for($j = 0; $j < count($map); $j++){ //para en j=18

                if($i == $map[$j]['mapIndex']){
                    $found = true;
                    $tmpArray[] = (string)$map[$j]['fileColumn']; //devuelve un string del valor de filecolum
                    break;
                    // return $map[$j]['mapIndex'];
                }


            }
            if(!$found){

                $tmpArray[] = null;
            } 
           
        }
        // dd($tmpArray);
        for($i = 0; $i < count($fileArray); $i++){

            $mapped[] =  [
                'matched' => 0,     //1
                'item_id' => 0,     //2
                'tx_type_id' => null,       //3
                'tx_type_name' => null,     //4
                'cuenta_externa' => '',   //8
                'fecha_movimiento' => $tmpArray[1]==null?null:$fileArray[$i][$tmpArray[1]],
                'descripcion' => $tmpArray[2]==null?null:$fileArray[$i][$tmpArray[2]],
                'referencia_1' => $tmpArray[4]==null?null:$fileArray[$i][$tmpArray[4]],
                'referencia_2' => $tmpArray[5]==null?null:$fileArray[$i][$tmpArray[5]],
                'referencia_3' => $tmpArray[6]==null?null:$fileArray[$i][$tmpArray[6]],
                'otra_referencia' => $tmpArray[7]==null?null:$fileArray[$i][$tmpArray[7]],
                'saldo_actual' => $tmpArray[8]==null?null:(float)$fileArray[$i][$tmpArray[8]],
                'valor_debito' => $tmpArray[9]==null?null:(float)$fileArray[$i][$tmpArray[9]],
                'saldo_anterior' => $tmpArray[10]==null?null:(float)$fileArray[$i][$tmpArray[10]],
                'valor_credito' => $tmpArray[11]==null?null:(float)$fileArray[$i][$tmpArray[11]],
                'valor_debito_credito' => $tmpArray[12]==null?null:(float)$fileArray[$i][$tmpArray[12]],
                'codigo_usuario' => $tmpArray[13]==null?null:$fileArray[$i][$tmpArray[13]],
                'nombre_agencia' => $tmpArray[14]==null?null:$fileArray[$i][$tmpArray[14]],
                'nombre_centro_costos' => $tmpArray[15]==null?null:$fileArray[$i][$tmpArray[15]],
                'codigo_centro_costo' => $tmpArray[16]==null?null:$fileArray[$i][$tmpArray[16]],
                'numero_comprobante' => $tmpArray[17]==null?null:$fileArray[$i][$tmpArray[17]],
                'nombre_usuario' => $tmpArray[18]==null?null:$fileArray[$i][$tmpArray[18]],
                'nombre_cuenta_contable' => $tmpArray[19]==null?null:$fileArray[$i][$tmpArray[19]],
                'numero_cuenta_contable' => $tmpArray[20]==null?null:$fileArray[$i][$tmpArray[20]],
                'nombre_tercero' => $tmpArray[21]==null?null:$fileArray[$i][$tmpArray[21]],
                'identificacion_tercero' => $tmpArray[22]==null?null:$fileArray[$i][$tmpArray[22]],
                'fecha_ingreso' => $tmpArray[23]==null?null:$fileArray[$i][$tmpArray[23]],
                'fecha_origen' => $tmpArray[24]==null?null:$fileArray[$i][$tmpArray[24]],
                'oficina_origen' => $tmpArray[25]==null?null:$fileArray[$i][$tmpArray[25]],
                'oficina_destino' => $tmpArray[26]==null?null:$fileArray[$i][$tmpArray[26]],
                'local_account' => $tmpArray[27]==null?null:$fileArray[$i][$tmpArray[27]],
                'numero_lote' => $tmpArray[28]==null?null:$fileArray[$i][$tmpArray[28]],
                'consecutivo_lote' => $tmpArray[29]==null?null:$fileArray[$i][$tmpArray[29]],
                'tipo_registro' => $tmpArray[30]==null?null:$fileArray[$i][$tmpArray[30]],
                'ambiente_origen' => $tmpArray[31]==null?null:$fileArray[$i][$tmpArray[31]],
                'beneficiario' => $tmpArray[32]==null?null:$fileArray[$i][$tmpArray[32]],
            ];

            // dd($mapped);
        }


        return $mapped;

    }




    private function getLocalIniSaldos($file){

        $user = Auth::user();

        $localInsert = $this->getIniInsertLocalArray($file);
        
        $this->createTmpTableConciliarLocalValues();

        DB::table($this->conciliar_tmp_local_values_table)->insert($localInsert);
        
        $query = DB::table($this->conciliar_tmp_local_values_table)
                ->select(DB::raw("SUM(valor_credito) as credit,SUM(valor_debito) as debit, ".$this->conciliar_tmp_local_values_table.".local_account"))
                ->join('accounts', $this->conciliar_tmp_local_values_table.'.local_account','=','accounts.local_account')
                ->join('banks', 'accounts.bank_id','=','banks.id')
                ->where('accounts.company_id','=',$user->current_company)
                ->groupBy('local_account')
                ->get();

        Schema::dropIfExists($this->conciliar_tmp_local_values_table);
        return $query;
        
    }

    private function getIniInsertLocalArray($file){

        $user = Auth::user();
        $localInsert = null;

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
        $spreadsheet->setActiveSheetIndex(1);
        $worksheet = $spreadsheet->getActiveSheet();
        $highestRow = $worksheet->getHighestDataRow();
        $highestColumn = $worksheet->getHighestColumn();
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
        $externalInsert = [];


        for($row = 3; $row <= $highestRow; $row++){
            
            $cell = array();
            $insertCell = array();


            for($col = 1; $col <= CONTABLE_COLS; $col++){
                
                $typeCell = $worksheet->getCellByColumnAndRow($col, $row)->getDataType();

                switch($typeCell){
                    
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
                        if( \PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($valueCell = $worksheet->getCellByColumnAndRow($col, $row))){
                            
                            $tmpValueCell = date("Y-m-d H:i:s",\PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($tmpValueCell));
                            
                        }
                        
                        $valueCell = $tmpValueCell;
                        
                    break;
                    
                    default:
                        $tmpValueCell = $valueCell = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
                    break;
                }


                $cell[] = ["value"=>$valueCell,"type"=>$typeCell];
                    
                $insertCell[] = ($valueCell===null)?null:$valueCell;
                
            }
            
            
            $tx_type_id = null;
            $tx_type_name = null;

            if($insertCell[2] ===null){

                continue;
            }


            $tx_type_talbe_obj = new LocalTxType($this->conciliar_local_tx_type);
            $tx_type_table = $tx_type_talbe_obj->where('description','like', '%'.strtoupper($insertCell[8]).'%')
                        ->get();


            if(count($tx_type_table) != 0){

                $tx_type_id = $tx_type_table[0]->id;
                $tx_type_name = $tx_type_table[0]->tx;
                
            }

            
            
            if($insertCell[5] == null && $insertCell[6] == null && $insertCell[7] == null ){


            }

            
            $localInsert[] = [
                     'tx_type_id'=>$tx_type_id,
                     'tx_type_name'=>$tx_type_name,
                     'local_account'=>$insertCell[0],
                     'cuenta_externa'=>$insertCell[3],
                     'fecha_movimiento'=> date('Y-m-d',strtotime($insertCell[4])),
                     'numero_comprobante'=> $insertCell[5],
                     'referencia_1'=>$insertCell[6],
                     'identificacion_tercero'=>$insertCell[7],
                     'descripcion'=>$insertCell[8],
                     'valor_debito'=>$insertCell[9],
                     'valor_credito'=>$insertCell[10],
                    ];

        }

        return $localInsert;
    }
    private function getIniInsertExternalArray($file){

        $user = Auth::user();
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
        $spreadsheet->setActiveSheetIndex(0);
        $worksheet = $spreadsheet->getActiveSheet();
        $highestRow = $worksheet->getHighestRow();
        $highestColumn = $worksheet->getHighestColumn();
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
        $externalInsert = [];

        for($row = 3; $row <= $highestRow; $row++){
            
            $cell = array();
            $insertCell = array();


            for($col = 1; $col < RECAUDO_COLS; $col++){
                
                $typeCell = $worksheet->getCellByColumnAndRow($col, $row)->getDataType();

                switch($typeCell){
                    
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
                        if( \PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($valueCell = $worksheet->getCellByColumnAndRow($col, $row))){
                            
                            $tmpValueCell = date("Y-m-d H:i:s",\PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($tmpValueCell));
                            
                        }
                        
                        $valueCell = $tmpValueCell;
                        
                    break;
                    
                    default:
                        $tmpValueCell = $valueCell = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
                    break;
                }


                $cell[] = ["value"=>$valueCell,"type"=>$typeCell];
                    
                $insertCell[] = ($valueCell===null)?null:$valueCell;
                

            }

            $tx_type_id = null;
            $tx_type_name = null;

            if($insertCell[3] ===null ||  $insertCell[3] == ""){

                continue;
            }
            

            $account = Account::where('bank_account',$insertCell[2])
                        ->with('banks')
                        ->first();

            if($account == null){

                $error = \Illuminate\Validation\ValidationException::withMessages(
                            ['No existe una cuenta externa con número: '.$insertCell[2],
                ]);
                throw $error;
            }

            $tx_type_table = ExternalTxType::where('bank_id',$account->bank_id)
                        ->where('description','=', trim($insertCell[7]))
                        ->get();

            $account = Account::where('bank_account',$insertCell[2])
                        ->with('banks')
                        ->first();

         
            
            if(count($tx_type_table) > 0){

                $tx_type_id = $tx_type_table[0]->id;
                $tx_type_name = $tx_type_table[0]->tx;
                
            }

            $tx_type_table = ExternalTxType::where('type','COMPUESTO')->get();

            for($i = 0; $i < count($tx_type_table); $i++){

                if (strpos(strtoupper($insertCell[7]), $tx_type_table[$i]->description) !== false) {
                    
                    $tx_type_id = $tx_type_table[$i]->id;
                    $tx_type_name = $tx_type_table[$i]->tx;
                    break;
                }
                
            }

            if($tx_type_id === null){


                $error = \Illuminate\Validation\ValidationException::withMessages(
                   ['No existe una transacción con descripción: '.strtoupper($insertCell[7])
                ]);
                throw $error;
            }
       
            
            $externalInsert[] = [
                     'tx_type_id'=>$tx_type_id,
                     'tx_type_name'=>$tx_type_name,
                     'numero_cuenta'=>$insertCell[2],
                     'fecha_movimiento'=> date('Y-m-d',strtotime($insertCell[3])),
                     'referencia_1'=>$insertCell[4],
                     'referencia_2'=>$insertCell[5],
                     'referencia_3'=>$insertCell[6],
                     'descripcion'=>$insertCell[7],
                     'valor_credito'=>$insertCell[8],
                     'valor_debito'=>$insertCell[9],
                    ];
            

        }

        return $externalInsert;
    }


    private function getExternalIniSaldos($file){

        $user = Auth::user();

        $externalInsert = $this->getIniInsertExternalArray($file);

        $this->createTmpTableConciliarExternalValues();

        DB::table($this->conciliar_tmp_external_values_table)->insert($externalInsert);
        
        $query = DB::table($this->conciliar_tmp_external_values_table)
                ->select(DB::raw("SUM(valor_credito) as credit,SUM(valor_debito) as debit, numero_cuenta,
                    banks.name, accounts.local_account, banks.name"))
                ->join('accounts', $this->conciliar_tmp_external_values_table.'.numero_cuenta','=','accounts.bank_account')
                ->join('banks', 'accounts.bank_id','=','banks.id')
                ->where('accounts.company_id','=',$user->current_company)
                ->groupBy('numero_cuenta','banks.name','accounts.local_account','bank_account')
                ->orderBy('banks.name','DESC')
                ->orderBy('numero_cuenta','ASC')
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
