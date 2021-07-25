<?php

namespace App\Http\Controllers\Company;


use App\Models\User;
use App\Models\Company;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\ApiController;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Conciliar\ConciliarController;

class CompanyController extends ApiController
{   


    public function __construct(){

        
        $this->middleware('auth:api')->only(['show','update','setMap','getCompanyInfo','store', 'index']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $user = Auth::user();//auth propiedad global del passport siempre y cuando en el constructor se haya llamado en middleware. Obj completo de usuario (usuario loggeado)
        $companies = Company::all();

        if($user->type == User::SUPER_ADMIN){

            //$companies = Company::with('locations')->get();
            return $this->showAll($companies,'',true);

        }else{

            $companies = Company::where('id',$user->current_company)->get();   
            return $this->showAll($companies,'',true);
        }

        
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
    public function store(Request $request){

        $rules = [
            'nit'=>'required|string|unique:companies',
            'name'=>'required|string',
            'sector'=>'required|string',
            'address'=>'required|string',
            'phone'=>'required|string',
            'country_id'=>'required|integer',
            'state_id'=>'required|integer',
            'city_id'=>'required|integer',
            
        ];

        $request->validate($rules);

        $tmpFields = $request->all();

        $location = [
                'country_id'=>$tmpFields['country_id'],
                'state_id'=>$tmpFields['state_id'],
                'city_id'=>$tmpFields['city_id'],

            ];

        $location = Location::create($location);

        $companyFields = [
            'nit'=>$tmpFields['nit'],
            'name'=>$tmpFields['name'],
            'sector'=>$tmpFields['sector'],
            'address'=>$tmpFields['address'],
            'phone'=>$tmpFields['phone'],
            'location_id'=>$location->id,
        ];

        $company = Company::create($companyFields);    

        $this->createTables($company->id);
        

        return $this->showOne($company,'',true);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Company $company)
    {

        return $this->showOne($company,'',true);
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
    public function update(Request $request, Company $company)
    {
        // $company = Company::findOrFail($id);

        $location = Location::findOrFail($company->location_id);
        // return $this->showOne($company);
        $rules = [
            'nit'=>'required|unique:companies,nit,'.$company->id,
            'name'=>'required|string',
            'sector'=>'required|string',
            'address'=>'required|string',
            'phone'=>'required|string',
            'country_id'=>'required|integer',
            'state_id'=>'required|integer',
            'city_id'=>'required|integer',
            
        ];

        $request->validate($rules);

        $company->nit = $request->nit;
        $company->name = $request->name;
        $company->sector = $request->sector;
        $company->address = $request->address;
        $company->phone = $request->phone;
        $company->location_id = $request->location_id;

        
        $location->country_id = $request->country_id;
        $location->state_id = $request->state_id;
        $location->city_id = $request->city_id;
        

        $location->save();
        $company->save();

        return $this->showOne($company);
      

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

    public function setMap(Request $request){

        $fields = $request->all();

        $company = Company::find($fields['company_id']);

        $company->map_id = $fields['map_id'];

        $company->save();
        
        return $this->showOne($company);

    }

    public function getCompanyInfo(){

        $user = Auth::user();


        $company = COMPANY::with('locations')->find($user->current_company);

        return $this->showOne($company);

    }

    private function createTables($id){

        //return 'hola';

        $user = Auth::user();

        $conciliarController = new ConciliarController();

        $user->current_company = $id;
        $conciliarController->init($user);

        return $conciliarController->createTablesInit();
    }


}
