<?php

namespace App\Http\Controllers\MapFile;

//use \Excel;
use App\Models\MapFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Imports\ConciliarIniImport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;
use App\Http\Controllers\ApiController;

class MapFileController extends ApiController
{

    const ARRAY_SEPARATOR = array(',',';','|');
    public function __construct(){

        
        $this->middleware('auth:api')->only(['index','show','update','store','uploadFile','saveMap',
            'getMapIndex']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $user = Auth::user();


        $mapFiles = MapFile::with('users')
                ->with('banks') 
                ->where('company_id', (int)$user->current_company)
                ->orWhere('type', MapFile::TYPE_CONCILIAR_EXTERNO)
                ->get();

        return $this->showAll($mapFiles);
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
    public function update(Request $request, MapFile $mapFile)
    {   

        $rules = [
            'bank_id'=>'required',
            'base'=>'required',
            'description'=>'required',
            'type'=>'required',
            'map'=>'required',
        ];

        $request->validate($rules);

        $fields = $request->all();
        
        if($fields['type'] == MapFile::TYPE_CONCILIAR_EXTERNO && !$fields['bank_id']){

            $error = \Illuminate\Validation\ValidationException::withMessages([
               'bank_id' => ['El campo bank_id es obligatorio para archivos exgternos'],
            ]);
            throw $error;

        }

        if($fields['type'] == MapFile::TYPE_CONCILIAR_INTERNO){

             $mapFile->bank_id = null;
        }else{

            $mapFile->bank_id = $fields['bank_id'];
        }

        
        $mapFile->description = $fields['description'];
        $mapFile->type = $fields['type'];
        $mapFile->base = $fields['base'];
        $mapFile->map = $fields['map'];


        $mapFile->save(); 

        return $this->showOne($mapFile); 
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

    public function getMapIndex($type){

        if($type == 'conciliar_externo'){

             $bankMap = DB::Table('map_bank_index')
                    ->orderBy('description')
                    ->get();

             return $this->showAll($bankMap);

        }else{


            $localMap = DB::Table('map_local_index')
                    ->orderBy('description')
                    ->get();

             return $this->showAll($localMap);
        }
       
    }

    public function saveMap(Request $request){

        $user = Auth::user();


        $rules = [
            'bank_id'=>'required',
            'description'=>'required',
            'type'=>'required',
            'map'=>'required',
        ];



        $request->validate($rules);


        $fields = $request->all();

        

        if($fields['type'] == MapFile::TYPE_CONCILIAR_EXTERNO && !$fields['bank_id']){

            $error = \Illuminate\Validation\ValidationException::withMessages([
               'bank_id' => ['El campo bank_id es obligatorio para archivos exgternos'],
            ]);
            throw $error;

        }

        if($fields['type'] == MapFile::TYPE_CONCILIAR_INTERNO){

             $fields['bank_id'] = null;
        }
        $fields['created_by'] = $user->id;
        $fields['header'] = false;
        $fields['company_id'] = (int)$user->current_company;
        $fields['separator'] = "";
        $fields['extension'] = "";

        // return $fields;
        $mapFile = MapFile::create($fields);

        return $this->showOne($mapFile);
    }

    public function uploadFile(Request $request){


        $user = Auth::user();

        $ext = $request->file->extension()=='txt'?'csv':$request->file->extension();
            
        
        $request->file->storeAs('tmpUpload',$user->id.'mapFile.'.$ext,'local');


        $file = storage_path('app/tmUpload/'.$user->id.'mapFile.'.$ext);

        if($ext == 'csv' || $ext == 'txt'){

            $content = $this->getFormatSeparator($request->file);
            return $this->showArray($content);
        }
     
        $excel = \Excel::toArray(null, $request->file);

        $tmpArray = [$excel[0][0],$excel[0][1]];


        return $this->showArray($tmpArray);

    }

    public function formatsExterno($id){

        $formats = MapFile::where('bank_id',$id)
                    ->orWhere('bank_id',29)
                    ->get();

        return $this->showAll($formats);
    }

    public function formatsLocal($id){

        $formats = MapFile::where('company_id',$id)
                    ->where('type',MapFile::TYPE_CONCILIAR_INTERNO)
                    ->get();

        return $this->showAll($formats);
    }

    private function getFormatSeparator($file){

        $content = array();
        if (($gestor = $this->utf8_fopen_read($file)) !== FALSE) {

            $tmpArray = MapFileController::ARRAY_SEPARATOR;
            
            for($i = 0; $i < count($tmpArray); $i++){
                $content = array();
                $gestor = $this->utf8_fopen_read($file);
                for($j = 0; $j < 2; $j++){
                        
                    if($gestor){
                        
                        $content[] = fgetcsv($gestor, 0, $tmpArray[$i]);
                        
                    }
                    
                }

                
                if(is_array($content[0]) && count($content[0]) > 4){

                    return $content;
                }


            }

            $error = \Illuminate\Validation\ValidationException::withMessages([
           'separador' => ['Solo se permiteeen los siguiente separadorees: '.json_encode(MapFileController::ARRAY_SEPARATOR)],
            ]);
            throw $error; 
            }

    }





}
