<?php

namespace App\Http\Controllers\Cartera;

use App\ConfecarItem;
use App\ConfecarHeader;
use Illuminate\Http\Request;
use App\Imports\ConfecarImport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\ApiController;

class ConfecarHeaderController extends ApiController
{
    protected $confecarHeaders = '';
    protected $confecarItems = '';
    protected $capitalInteres = '';
    protected $capitalProvision = '';
    protected $capitalProvisionIntere = '';
    protected $capitalProvisionOtros = '';

    public function __construct(){

        $this->middleware(function ($request, $next) {

            $user = Auth::user();

            $this->confecarHeaders = 'confecar_headers_'.$user->current_company;
            $this->confecarItems = 'confecar_items_'.$user->current_company;
            $this->capitalInteres = 'capital_interes_'.$user->current_company;
            $this->capitalProvision = 'capital_provision_'.$user->current_company;
            $this->capitalProvisionIntere = 'capital_provision_interes'.$user->current_company;
            $this->capitalProvisionOtros = 'capital_provision_otros'.$user->current_company;
            

            return $next($request);
        });

        
        $this->middleware('auth:api')->only(
                [
                    'createTables',
                    'uploadCONFECAR',
                   
                ]);

    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        //
    }


    public function uploadCONFECAR(Request $request){

        ini_set('memory_limit', '-1');

        $user = Auth::user();

        $date = date('Y-m',strtotime($request->fecha)).'-01';


        $confecarHeader = new ConfecarHeader($this->confecarHeaders);
        
        $header = $confecarHeader->where('fecha',$date)->first();

        

        if ($header == null) {


            $headerInsert = new ConfecarHeader($this->confecarHeaders);
            
            $insertValues = [
                'fecha' => $date,
                'file_name' => $request->file->getClientOriginalName(),
                'file_path' => $request->file->store($user->current_company.'/cartera','cuadres'),

            ];

            
            $headerInsert->insert($insertValues);

            $confecarHeader = new ConfecarHeader($this->confecarHeaders);

            $header = $confecarHeader->where('fecha',$date)->first();
            
        }else{

            $header->file_path = $request->file->store($user->current_company.'/cartera','cuadres');
            $header->save();
        }


        $confecarItems = new ConfecarItem($this->confecarItems);
        $confecarItems->where('header_id',$header->id)->delete();  

        $array = Excel::toArray(new ConfecarImport, public_path('cuadres/'.$header->file_path));
        
        $confecar = $array[0];
        $rowInsert = array();
        $Date = "01-01-1900";
        for($i = 1; $i < count($confecar); $i++){

            return date('Y-m-d', strtotime($Date. ' + '.$confecar[$i][16].' days'));
            // return [$confecar[$i][16],date('Y-m-d',$confecar[$i][16]),$confecar[$i][17]];
            $rowInsert[] = "(".$header->id.",'".$confecar[$i][1]."','".$confecar[$i][5]."','".$confecar[$i][6]."','".$confecar[$i][8]."','".$confecar[$i][9]."','".$confecar[$i][13]."','".$confecar[$i][14]."','".date('Y-m-d H:i:s',strtotime($confecar[$i][16]))."','".$confecar[$i][17]."',".$confecar[$i][32].",".$confecar[$i][33].",".$confecar[$i][34].",".$confecar[$i][36].",".$confecar[$i][37].",".$confecar[$i][38].",".$confecar[$i][39].",".$confecar[$i][40].",".$confecar[$i][41].",'".$confecar[$i][42]."',".$confecar[$i][44].",'".$confecar[$i][48]."',".$confecar[$i][49].",".$confecar[$i][50].",".$confecar[$i][51].",".$confecar[$i][52].",".$confecar[$i][53].",".$confecar[$i][54].",".$confecar[$i][55].",".$confecar[$i][56].",'".$confecar[$i][59]."',".$confecar[$i][62].",'".$confecar[$i][65]."','".$confecar[$i][69]."','".$confecar[$i][87]."','".$confecar[$i][88]."')";


        }

        $strInsert = "INSERT INTO ".$this->confecarItems."
                    (header_id,B,F,G,I,J,N,O,Q,R,AG,AH,AI,AK,AL,AM,AN,AO,AP,AQ,`AS`,
                    AW,AX,AY,AZ,BA,BB,BC,BD,BE,BH,BK,BN,BR,CJ,CK)
                    VALUES
                    " .$rowInsert[0];//implode(',',$rowInsert);
        
        return $strInsert;
        DB::insert($strInsert);
    }



    public function createTables(){

        // return $this->showMessage("No se creó ninguna tablas");
        $this->createTableConfecarHeaders();
        $this->createTableConfecarItems();
        $this->createTableCapitalInteres();
        $this->createTableCapitalProvision();
        $this->createTableCapitalProvisionInteres();
        $this->createTableCapitalProvisionOtros();
    }

    public function createTableConfecarHeaders(){

        Schema::dropIfExists($this->confecarHeaders);

        Schema::create($this->confecarHeaders, function($table)  {
            $table->bigIncrements('id');
            $table->date('fecha')->index();
            $table->string('status')->default('CERRADO');
            $table->string('file_name');
            $table->string('file_path');
               

            $table->softDeletes();
            $table->timestamps();


        });
    }

    public function createTableConfecarItems(){

        Schema::dropIfExists($this->confecarItems);

        Schema::create($this->confecarItems, function($table)  {
            $table->bigIncrements('id');
            $table->bigInteger('header_id')->index();
            $table->string('B')->index()->comment('No identificacion');
            $table->string('F')->comment('categ');
            $table->string('G')->comment('Gtía 0=Otras 1=Admis');
            $table->string('I')->comment('Restructurado');
            $table->string('J')->comment('Numero credito');
            $table->string('N')->comment('código cont_1');
            $table->string('O')->comment('Lin');
            $table->date('Q')->comment('fecha desembolso')->nullable();
            $table->date('R')->comment('fecha desembolso');
            $table->decimal('AG',24,4)->comment('Tasa Int.  Nominal Anual');
            $table->decimal('AH',24,4)->comment('Tasa Int.  Nominal Anual');
            $table->decimal('AI',24,2)->comment('Valor Préstamo');
            $table->decimal('AK',24,2)->comment('Saldo Capital');
            $table->decimal('AL',24,2)->comment('Ints');
            $table->decimal('AM',24,2)->comment('Otros OK');
            $table->decimal('AN',24,2)->comment('costas (reporte de costas)');
            $table->decimal('AO',24,2)->comment('Seguros');
            $table->string('AP')->comment('Gtías Prend ok');
            $table->string('AQ')->comment('Gtía_Depósitos Fideicomiso');
            $table->decimal('AS',24,2)->comment('valor garantia distinta a aportes sociales');
            $table->date('AW')->comment('Fecha último Avalúo');
            $table->decimal('AX',24,2)->comment('Provision Capital');
            $table->decimal('AY',24,2)->commeent('Provision Ints y Otros ');
            $table->decimal('AZ',24,2)->comment('Provision k');
            $table->decimal('BA',24,2)->comment('Provision Ints');
            $table->decimal('BB',24,2)->comment('Provision Otros');
            $table->decimal('BC',24,2)->comment('Provisión Costas (reporte de costas)');
            $table->decimal('BD',24,2)->comment('Provisión Seguro');
            $table->decimal('BE',24,2)->comment('Contingencias');
            $table->date('BH')->comment('Fecha Ultimo Pago');
            $table->string('BK')->comment('Clase Garantia');
            $table->string('BN')->comment('Oficina')->index();
            $table->decimal('BR',24,2)->comment('Valor Capital Mora ');
            $table->string('CJ')->comment('Nit Deudora Patronal');
            $table->string('CK')->comment('Nombre Deudora Patronal');
            
            $table->timestamps();


        });

    }

    public function createTableCapitalInteres(){

        Schema::dropIfExists($this->capitalInteres);

        Schema::create($this->capitalInteres, function($table)  {
            $table->bigIncrements('id');
            $table->string('capital')->index();
            $table->string('interes')->index();
            
            $table->unique(['capital', 'interes']);
            $table->timestamps();


        });

    }

    public function createTableCapitalProvision(){

        Schema::dropIfExists($this->capitalProvision);

        Schema::create($this->capitalProvision, function($table)  {
            $table->bigIncrements('id');
            $table->string('capital')->index();
            $table->string('provision')->index();
            
            $table->unique(['capital', 'provision']);
            $table->timestamps();


        });

    }

    public function createTableCapitalProvisionInteres(){

        Schema::dropIfExists($this->capitalProvisionIntere);

        Schema::create($this->capitalProvisionIntere, function($table)  {
            $table->bigIncrements('id');
            $table->string('capital')->index();
            $table->string('provision_interes')->index();
            
            $table->unique(['capital', 'provision_interes']);
            $table->timestamps();


        });

    }

    public function createTableCapitalProvisionOtros(){

        Schema::dropIfExists($this->capitalProvisionOtros);

        Schema::create($this->capitalProvisionOtros, function($table)  {
            $table->bigIncrements('id');
            $table->string('capital')->index();
            $table->string('provision_otros')->index();
            
            $table->unique(['capital', 'provision_otros']);
            $table->timestamps();


        });

    }
}
