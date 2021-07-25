<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\ApiController;

class UserCompanyController extends ApiController
{

    public function __construct(){

        
        $this->middleware('auth:api')->except(['getCompaniesByEmail']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()   
    {

        $user = Auth::user();

        switch ($user->type) {
            case User::SUPER_ADMIN:
                
                $companies = Company::all();
                return $this->showAll($companies);

                break;

            case User::ADMIN:
            case User::USER:
                
                $companies = $user->companies;
                return $this->showAll($companies);
                
                break;
            
            default:
                return $this->showAll([]);
                break;
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::find($id);
        return $user->companies;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
    }

    public function companiesToUser(Request $request){

        $user = User::find($request->all()['id']);

        $array = json_decode($request->all()['info'], true);

        $setArray = array();
        $unSetArray = array();
        foreach( $array as $value){

            if($value['selected']){

                $setArray[] = $value['company'];
            }
        }

        // $user->companies()->syncWithoutDetaching($setArray);
        $user->companies()->sync($setArray);
        return $setArray;
    }
    //Método que trae la lista de empresas a las cuales está vinculado un usuario para entrar al aplicativo
    public function getCompaniesByEmail($email){

        $user = User::where('email',$email)->first();

        if($user){

            $companies = $user->companies;


            return $this->showAll($companies,'',true);
        }else{

            return $this->showMessage('',false);
        }
        

    }

    public function getUserCompanies(){
        
        $userOath = Auth::user();

        $userCompanies = User::with('companies')->find($userOath->id);

        return $this->showArray($userCompanies['companies']);
    }

    public function setUserCurrentCompany(Request $request){

        $userOath = Auth::user();
        $form = $request->all();

        $userDB = User::where('id',$userOath->id)->first();

        if (password_verify($request->password, $userDB->password)){

            $userDB->current_company = $request->center;
            $userDB->save();

            return $this->showMessage('Cambio realizado',true);
        }else{

            return $this->showMessage('Contraseña Incorrecta',false);
            
        }
        



    }

}
