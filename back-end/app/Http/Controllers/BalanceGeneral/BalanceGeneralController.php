<?php

namespace App\Http\Controllers\BalanceGeneral;

set_time_limit(300);

use App\Models\BalanceGeneralHeader;
use App\Models\BalanceGeneralItem;
use App\Models\ConvenioCuadre;
use App\Exports\CuadreBalanceGeneralExport;
use App\Http\Controllers\ApiController;
use App\Imports\BalanceGeneralImport;
use App\Imports\OperativoConvenioImport;
use App\OperativoConvenioHeader;
use App\OperativoConvenioItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;

class BalanceGeneralController extends ApiController
{   

    protected $balance_general_headers = '';
    protected $balance_general_items = '';
    protected $convenios_headers = '';
    protected $convenios_items = '';
    protected $convenios_cuentas = '';
    protected $operativo_cuentas = '';
   

    public function __construct(){

        $this->middleware(function ($request, $next) {

            $user = Auth::user();

            $this->balance_general_headers = 'balance_general_headers_'.$user->current_company;
            $this->balance_general_items = 'balance_general_items_'.$user->current_company;
            $this->convenios_headers = 'convenios_headers_'.$user->current_company;
            $this->convenios_items = 'convenios_items_'.$user->current_company;
            $this->convenios_cuentas = 'convenios_cuentas_'.$user->current_company;
            $this->operativo_cuentas = 'operativo_cuentas_'.$user->current_company;
            
            

            return $next($request);
        });

        
        $this->middleware('auth:api')->only(
                [
                    'index',
                    'createTables',
                    'uploadBalance',
                    'uploadConvenios',
                    'getBalance',
                    'downloadConvenio',
                    'downloadBalance',
                    'downloadConvenioResultado'
                 
                ]);



    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $headerTable = new BalanceGeneralHeader($this->balance_general_headers);

        $headers = $headerTable->orderBy('fecha','desc')->get();

        return $this->showAll($headers);
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

    /*
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }


    public function getBalance(Request $request){

        
        $infoArray = $this->getBalanceResult($request->fecha);
        return $infoArray;
        if(count($infoArray) > 0){

            return $this->showArray($infoArray, true);
       }else{


            return $this->showMessage("",false);
        }  
        
    }

    public function getBalanceResult($fechaRequest){



        $balanceHeaderTable = new BalanceGeneralHeader($this->balance_general_headers);//hereda de header funciones, variables, etc
        return $this->balance_general_headers;
        $balanceHeader = $balanceHeaderTable->where('fecha',$fechaRequest)->first();//se hace uso del objeto para buscar en la bd
        
        if(!$balanceHeader == null){

            $convenioHeaderTable = new OperativoConvenioHeader($this->convenios_headers);
            $convenioHeader = $convenioHeaderTable->where('fecha',$fechaRequest)->first();

            $items = [];

            if($convenioHeader != null){

                $items = DB::table($this->convenios_items)
                    ->select(DB::raw("SUM(convenios_items_1.salcuo) AS sum_salcuo,convenios_items_1.header_id,convenios_items_1.numcon"))
                    ->where('header_id',$convenioHeader->id)
                    ->groupBy('numcon','header_id','numcon')
                    ->get();

            }
            

            $convenioCuentasTable = new ConvenioCuadre($this->convenios_cuentas);
            $cuentas = $convenioCuentasTable->get();
            
            $balanceItemsTable = new BalanceGeneralItem($this->balance_general_items);
            // return $convenioHeader->id;
            $balanceItems = $balanceItemsTable
                            ->select('cuenta','nombre_cuenta','saldo_actual')
                            ->orWhere(function($query ) use ($cuentas){
                                for($i = 0; $i < count($cuentas); $i++){
                                    $query->orWhere('cuenta',$cuentas[$i]['cuenta']);
                                }
                             })
                            ->where('header_id',$balanceHeader->id)
                            ->get();
            
            // $balanceItems = $balanceItemsTable
            //             ->select('cuenta','nombre_cuenta','saldo_actual')
            //             ->orWhere(function($query){
            //                  $query->orWhere('cuenta','147315100501')
            //                         ->orWhere('cuenta','147315100502')
            //                         ->orWhere('cuenta','147315100505')
            //                         ->orWhere('cuenta','147315100507')
            //                         ->orWhere('cuenta','147315100508')
            //                         ->orWhere('cuenta','147315100509');
            //              })
            //             ->where('header_id',$balanceHeader->id)
            //             ->get();

            // return  $this->getBalanceNaturaleza($balanceHeader->id);
            $naturalezaCuentas = $this->getBalanceNaturaleza($balanceHeader->id);

            $infoArray = [
                    'balance'=> ['header'=>$balanceHeader, 
                                 'items' => $balanceItems,
                                ],
                    'nautralezaContable' => $naturalezaCuentas['CONTABILIDAD'],
                    'nautralezaOperativa' => $naturalezaCuentas['OPERATIVO'],
                    'convenios'=>['header'=>$convenioHeader,'items'=>$items],
                    'cuentasArray' => $cuentas,

                    
                ];

            return $infoArray;

        }else{


            return array();
        }
    }

    public function getBalanceNaturaleza($headerId){

        $responseBalance = array();


        $balance = DB::table($this->operativo_cuentas)
                     ->select(DB::raw(  $this->operativo_cuentas.".cuenta AS cuenta_maestro,
                                        ".$this->balance_general_items.".cuenta AS cuenta_balance,
                                        ".$this->operativo_cuentas.".area,
                                        ".$this->operativo_cuentas.".descripcion,
                                        ".$this->operativo_cuentas.".naturaleza,
                                        ".$this->operativo_cuentas.".tipo_saldo,
                                        ".$this->balance_general_items.".saldo_actual,
                                        ".$this->balance_general_items.".header_id"))
                     ->where('header_id', $headerId)
                     ->join($this->balance_general_items, 
                            $this->balance_general_items.'.cuenta', '=', $this->operativo_cuentas.".cuenta")
                     ->get();
                     
        $responseBalance['CONTABILIDAD'] = [];
        $responseBalance['OPERATIVO'] = [];
        foreach($balance as $row){

            
            switch ($row->naturaleza) {
                case 'DEBITO':
                    
                    if(trim($row->tipo_saldo) == 'Con saldo' && $row->saldo_actual < 0){

                        if($row->area == 'CONTABILIDAD'){

                            $responseBalance['CONTABILIDAD'][] = $row;
                        }else{

                            $responseBalance['OPERATIVO'][] = $row;
                        }
                        continue;
                    }

                    if(trim($row->tipo_saldo) == 'CEROS' && $row->saldo_actual != 0){

                        if($row->area == 'CONTABILIDAD'){

                            $responseBalance['CONTABILIDAD'][] = $row;
                        }else{

                            $responseBalance['OPERATIVO'][] = $row;
                        }
                        
                        continue;
                    }

                break;
                
                case 'CREDITO':
                    
                    if(trim($row->tipo_saldo) == 'Con saldo' && $row->saldo_actual > 0){

                        if($row->area == 'CONTABILIDAD'){

                            $responseBalance['CONTABILIDAD'][] = $row;
                        }else{

                            $responseBalance['OPERATIVO'][] = $row;
                        }
                        continue;
                    }

                    if(trim($row->tipo_saldo) == 'CEROS' && $row->saldo_actual != 0){

                        if($row->area == 'CONTABILIDAD'){

                            $responseBalance['CONTABILIDAD'][] = $row;
                        }else{

                            $responseBalance['OPERATIVO'][] = $row;
                        }
                        continue;
                    }
                    break;
                
                default:
                    # code...
                    break;
            }
            
        }

        return $responseBalance;
    }


    public function downloadConvenioResultado(Request $request){

        $result = $this->getBalanceResult($request->fecha);

        $infoArray = array();

        $infoArray['balanceItems'] = $result['balance']['items'];
        $infoArray['balanceHeader'] = $result['balance']['header'];
        $infoArray['nautralezaContable'] = $result['nautralezaContable'];
        $infoArray['nautralezaOperativa'] = $result['nautralezaOperativa'];
        $infoArray['convenios'] = $result['convenios'];
        $infoArray['cuentasArray'] = $result['cuentasArray'];

        $resultArray = $this->balanceConvenioResultToArray($infoArray);

        // return $resultArray;
        return (new CuadreBalanceGeneralExport($request->fecha, $resultArray))->download('CuadreBalanceGeneral.xlsx');

        return $infoArray;
    }

    public function balanceConvenioResultToArray($infoArray){


        // return $infoArray;
        $tmpArray = array();

        $convItems = $infoArray['convenios']['items'];
        $indices = $infoArray['cuentasArray'];
        $balanceItems = $infoArray['balanceItems'];
        $cuentas = $infoArray['cuentasArray'];


        for ($i = 0; $i < count($convItems); $i++) {
            $found = false;
            for($j = 0; $j < count($indices); $j++){
                //se busca que el indice esté en los convenios del archivo
                //En caso que no esté se agrega al arreglo de indices
                if($convItems[$i]->numcon == $indices[$j]->linea){

                    $indices[$j]->operativo = $convItems[$i]->sum_salcuo;
                    $found = true;
                    break;

                }
            }

            if($found){

                $tmpArray[] = [ 'linea'=> $convItems[$i]->numcon,'nombre'=>'','cuenta'=>''];
            }

        }

        $resultArray = array();
        $resultArray['balanceItems'][] = ['cuenta','linea','nombre','contable','operativo','diferencia'];

        for ($i = 0; $i < count($cuentas); $i++){

            for($j=0; $j < count($balanceItems); $j++){

                if($cuentas[$i]->cuenta == $balanceItems[$j]->cuenta){

                    $cuentas[$i]->operativo = (float)$cuentas[$i]->operativo;
                    $cuentas[$i]->contable = (float)$balanceItems[$j]->saldo_actual;
                    $cuentas[$i]->diferencia =  $cuentas[$i]->operativo - $balanceItems[$j]->saldo_actual;
                    $resultArray['balanceItems'][] = [
                            $cuentas[$i]->cuenta,
                            $cuentas[$i]->linea,
                            $cuentas[$i]->nombre,
                            $cuentas[$i]->contable,
                            $cuentas[$i]->operativo,
                            $cuentas[$i]->diferencia
                        ];
                }
            }
        }
        
        $resultArray['nautralezaContable'][] = ['Cuenta','Descripción','Naturaleza','Tipo de Saldo','Saldo Actual'];
        

        foreach($infoArray['nautralezaContable'] as $value){
            $resultArray['nautralezaContable'][] = [
                                        $value->cuenta_maestro,
                                        $value->descripcion,
                                        $value->naturaleza,
                                        $value->tipo_saldo,
                                        (float)$value->saldo_actual,
                                    ];
        }

        $resultArray['nautralezaOperativa'][] = ['Cuenta','Descripción','Naturaleza','Tipo de Saldo','Saldo Actual'];
        foreach($infoArray['nautralezaOperativa'] as $value){
            $resultArray['nautralezaOperativa'][] = [
                                        $value->cuenta_maestro,
                                        $value->descripcion,
                                        $value->naturaleza,
                                        $value->tipo_saldo,
                                        (float)$value->saldo_actual,
                                    ];
        }


        
        return $resultArray;
    }

    public function uploadConvenios(Request $request){

        ini_set('memory_limit', '-1');

        $user = Auth::user();


        $header_id = null;


        $conveniosHeaders = new  OperativoConvenioHeader($this->convenios_headers);

        $header = $conveniosHeaders->where('fecha',$request->fecha)->first();

        if ($header == null) {


            $headerInsert = new OperativoConvenioHeader($this->convenios_headers);
            
            $insertValues = [
                'fecha' => $request->fecha, 
                'file_name' => $request->file->getClientOriginalName(),
                'file_path' => $request->file->store($user->current_company.'/convenios','cuadres'),
                'status' => OperativoConvenioHeader::OPEN,
                'user' => $user->id,
            ];

            $headerInsert->insert($insertValues);

            $conveniosHeaders = new OperativoConvenioHeader($this->convenios_headers);

            $header = $conveniosHeaders->where('fecha',$request->fecha)->first();
            
        }else{

            $header->file_path = $request->file->store($user->current_company.'/convenios','cuadres');
            $header->save();
        }
        
        
        $deleteConvenios = new OperativoConvenioItem($this->convenios_items);
        $deleteConvenios->where('header_id',$header->id)->delete();  



        $array = Excel::toArray(
                    new OperativoConvenioImport, public_path('cuadres/'.$header->file_path));

        
        

        $insertArray = [];
        $rawInsert =array();
        
        for($i = 9; $i < count($array[0]); $i++){

            if($array[0][$i][0] == null || $array[0][$i][0] == 'NUMCON'){

                continue;
            }
            
            $tmpArray =[

                'header_id' => $header->id, 
                'numcon' => $array[0][$i][0], 
                'codcon' => $array[0][$i][1], 
                'nitcli' => $array[0][$i][2], 
                'nropag' => $array[0][$i][3], 
                'fecuo' => $array[0][$i][4], 
                'vlrcuo' => $array[0][$i][5], 
                'vlrpag' => $array[0][$i][6], 
                'salcuo' => $array[0][$i][7], 
                'fecaso' => $array[0][$i][8], 
                'fecpag' => $array[0][$i][9], 

            ]; 

            $insertArray[] = $tmpArray;
            
            $rawInsert[] = "(".$header->id.",'".$array[0][$i][0]."','".$array[0][$i][1]."','".$array[0][$i][2]."','".$array[0][$i][3]."','".$array[0][$i][4]."',".$array[0][$i][5].",".$array[0][$i][6].",".$array[0][$i][7].",'".$array[0][$i][8]."','".$array[0][$i][9]."')";

            
        }

        DB::disableQueryLog();

        $rawString = 'INSERT INTO '.$this->convenios_items.' 
                    (header_id,numcon,codcon,nitcli,nropag,fecuo,vlrcuo,vlrpag,salcuo,fecaso,fecpag)
                    VALUES 
                    '.implode(',',$rawInsert);


        DB::insert($rawString);
        // return  $rawString;
        $tableBalanceItems = new OperativoConvenioItem($this->convenios_items);
        // $tableBalanceItems->insert($insertArray);

        return  $tableBalanceItems->first();
    }


    public function downloadConvenio(Request $request){

        $conveniosHeaders = new  OperativoConvenioHeader($this->convenios_headers);

        return $conveniosHeaders->orderBy('fecha','desc')->get();

        $header = $conveniosHeaders->where('fecha',$request->fecha)->first();


        $path = public_path('cuadres/'.$header->file_path,'cuadres');
        $fileName = $header->file_name;
        $headers = array('Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => "attachment; filename='Report.xls'");

        return response()->download($path,$fileName);
        // return $header;
    }

    public function downloadBalance(Request $request){

        $balanceHeaders = new BalanceGeneralHeader($this->balance_general_headers);

        $header = $balanceHeaders->where('fecha',$request->fecha)->first();

        $path = public_path('cuadres/'.$header->file_path,'cuadres');
        $fileName = $header->file_name;
        $headers = array('Content-Type' => 'application/vnd.ms-excel',
     'Content-Disposition' => "attachment; filename='Report.xls'");
        
        return response()->download($path,$fileName);
        // return $header;
    }

    public function uploadBalance(Request $request){
        
        $user = Auth::user();
        $header_id = null;
        

        $balanceHeaders = new BalanceGeneralHeader($this->balance_general_headers);

        $header = $balanceHeaders->where('fecha',$request->fecha)->first();


        if ($header == null) {

            $headerInsert = new BalanceGeneralHeader($this->balance_general_headers);

            $insertValues = [
                'fecha' => $request->fecha, 
                'file_name' => $request->file->getClientOriginalName(),
                'file_path' => $request->file->store($user->current_company.'/balances','cuadres'),
                'status' => BalanceGeneralHeader::OPEN,
                'user' => $user->id,
            ];



            $headerInsert->insert($insertValues);

            $balanceHeaders = new BalanceGeneralHeader($this->balance_general_headers);

            $header = $balanceHeaders->where('fecha',$request->fecha)->first();
            
        }else{

            $header->file_path = $request->file->store($user->current_company.'/balances','cuadres');
            $header->save();
        }
        

        $deleteBalance = new BalanceGeneralItem($this->balance_general_items);
        $deleteBalance->where('header_id',$header->id)->delete();  

        $array = Excel::toArray(new BalanceGeneralImport, public_path('cuadres/'.$header->file_path));

        $insertArray = [];
        

        for($i = 5; $i < count($array[0]); $i++){

            $tmpArray =[

                'header_id' => $header->id, 
                'registro' => $array[0][$i][0], 
                'agencia' => $array[0][$i][1], 
                'cuenta' => $array[0][$i][2], 
                'nombre_cuenta' => $array[0][$i][3], 
                'saldo_anterior' => $array[0][$i][4], 
                'debito' => $array[0][$i][5], 
                'credito' => $array[0][$i][6], 
                'saldo_actual' => $array[0][$i][7], 

            ];

            $insertArray[] = $tmpArray;
            
        }
        

        $tableBalanceItems = new BalanceGeneralItem($this->balance_general_items);
        $tableBalanceItems->insert($insertArray);

        return $this->showArray($header);

    }


    public function createTables(){
        
        // $this->createTableBlanceGeneralHeader();
        // $this->createTableBlanceGeneralItems();
        // $this->createTableConveniosHeaders();
        // $this->createTableConveniosItems();
        // $this->CreateTableConveniosCuentas();
        // $this->CreateTableOperativoCuentas();
        $this->CreateTableConfecar();

    }

    public function createTableBlanceGeneralHeader(){

        Schema::dropIfExists($this->balance_general_headers);

        Schema::create($this->balance_general_headers, function($table)  {
            $table->bigIncrements('id');
            $table->dateTime('fecha');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('status');
            $table->string('user');


            $table->softDeletes();
            $table->timestamps();

        });
    }

    public function createTableBlanceGeneralItems(){

        Schema::dropIfExists($this->balance_general_items);

        Schema::create($this->balance_general_items, function($table)  {
            $table->bigIncrements('id');
            $table->bigInteger('header_id')->unsigned();
            $table->integer('registro')->nullable();
            $table->string('agencia')->nullable();
            $table->string('cuenta')->nullable();
            $table->string('nombre_cuenta')->nullable();
            $table->decimal('saldo_anterior',24,2)->nullable();
            $table->decimal('debito',24,2)->nullable();
            $table->decimal('credito',24,2)->nullable();
            $table->decimal('saldo_actual',24,2)->nullable();


            $table->softDeletes();
            $table->timestamps();

            $table->foreign('header_id')->references('id')->on($this->balance_general_headers);

        });
    }

    public function createTableConveniosHeaders(){

        Schema::dropIfExists($this->convenios_headers);

        Schema::create($this->convenios_headers, function($table)  {
            $table->bigIncrements('id');
            $table->dateTime('fecha');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('status');
            $table->string('user');

            $table->softDeletes();
            $table->timestamps();

        });
    }

    public function createTableConveniosItems(){

        Schema::dropIfExists($this->convenios_items);

        Schema::create($this->convenios_items, function($table)  {
            $table->bigIncrements('id');
            $table->bigInteger('header_id')->unsigned();
            $table->string('numcon')->nullable();
            $table->string('codcon')->nullable();
            $table->string('nitcli')->nullable();
            $table->string('nropag')->nullable();
            $table->string('fecuo')->nullable();
            $table->decimal('vlrcuo',24,2)->nullable();
            $table->decimal('vlrpag',24,2)->nullable();
            $table->decimal('salcuo',24,2)->nullable();
            $table->string('fecaso')->nullable();
            $table->string('fecpag')->nullable();


            $table->softDeletes();
            $table->timestamps();

            $table->foreign('header_id')->references('id')->on($this->convenios_headers);

        });

    }

    public function CreateTableConveniosCuentas(){

        Schema::dropIfExists($this->convenios_cuentas);

        Schema::create($this->convenios_cuentas, function($table)  {
            $table->bigIncrements('id');
            $table->string('cuenta')->index();
            $table->string('linea');
            $table->string('nombre');

            $table->softDeletes();
            $table->timestamps();


        });

    }

    public function CreateTableOperativoCuentas(){

        Schema::dropIfExists($this->operativo_cuentas);

        Schema::create($this->operativo_cuentas, function($table)  {
            $table->bigIncrements('id');
            $table->string('cuenta')->index();
            $table->string('area');
            $table->string('descripcion');
            $table->string('naturaleza');
            $table->string('tipo_saldo');

            $table->softDeletes();
            $table->timestamps();


        });

    }

    



}
