<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use App\Mail\newUser;
use App\Mail\newToken;
use App\Models\Company;
use App\Mail\addUserCompany;
use Illuminate\Http\Request;
use App\Traits\errorResponse;
use App\Mail\RecoveryUserPassword;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str as Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\ApiController;

class UserController extends ApiController
{
    use HasApiTokens, SoftDeletes;

    public function __construct(){

        $this->middleware('auth:api')->except(['verify','recoveryPssw']);
        //$this->middleware('auth:api')->only(['show','update','setMap','getCompanyInfo']);
    }

    /*public function hola(){

        return ('hola');
    }*/


    

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        
        $users = User::all();

        return $this->showAll($users,'',true);
    }

    

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        //return 'hola';
        $rules = [
            'email'=>'required|string',
            'names'=>'required|string',
            'last_names'=>'required|string',
            'current_company'=>'required|string|exists:App\Models\Company,id',//valide que la compañia exista en bd
            'type'=>'required|string',
            
        ];

        $request->validate($rules);

        /*$user = DB::table('users')->where('name', 'John')->first();

            return $user->email;*/

            $company = DB::table('users')->where('email', $Fields['email'])->first();

            //return $company = DB::table('users')->where('email', $Fields['email'])->value('current_company ')->get();
            
            if($request->has('current_company')){
            $user->current_company == $company->current_company; 
            return $this->errorResponse('El ususario ya existe y está asociado a este cco', 409);
            }
            
        $Fields = $request->all();//all me garantiza que solo traiga los datos que el usuario digitó

        $user = User::where('email',$Fields['email'])->first();//Orm laravel. Busca si el email de la petición ya existe en la base de datos, abstraer la bd y se vuelve un objeto. Objeto de usuario
        
        //eturn $user->companies()->find($Fields['current_company']);
        /*if($user) 
            return $user;
        else
            return 'No se encontró usuario';*/

        if(!$user){//si el objeto no viene vacio, null retorna falso evaluado. fue seteado y entro

            $Fields['verification_token'] = User::genVerificationToken();
            $Fields['verified'] = User::NO_VERIFIED;
            $Fields['password'] = bcrypt(Str::random(6));//bcrypt, por medio de una llave se encripta la información que viene en la petición. 
            
            $user = User::create($Fields);

            $company = Company::findOrFail((int)$Fields['current_company'])->first();//findOrFail, encuentre este registro si no está retorne un error 500. 

            $company->users()->syncWithoutDetaching([$user->id]);//existe la compañia, la trae y la relaciona con el id del usuario

            Mail::to($user)->send(new newUser($user));    

            

        }else {

            

            $company = Company::findOrFail((int)$Fields['current_company']);

            $company->users()->syncWithoutDetaching([$user->id]);

            Mail::to($user)->send(new addUserCompany($user,$company));
        }
        
        return $this->showOne($user,'',true);
    }

    /**

     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return $this->showOne($user,'',true);
    }

   
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        
  
        $reglas= [
            'email' => 'email|unique:users',
            'password' => 'min:6|confirmed',
            'type'=> 'in:' . User::SUPER_ADMIN . ',' . User::ADMIN . ',' . User::USER, 
        ];

        

        $this ->validate($request, $reglas);

        
        
        if($request->has('current_company')){
            $user->current_company = $request->current_company;
        }

        

        if($request->has('names')){
            $user->names = $request->names; //el atributo name será igual al requerido en la petición
        }   

        if($request->has('last_names')){
            $user->last_names = $request->last_names; //el atributo name será igual al requerido en la petición
        }

        if($request->has('email') && $user->email != $request->email){
                $user->verified = User::NO_VERIFIED;
                $user->verification_token = User::genVerificationToken();
                $user->email = $request->email;
        }


        if($request->has('password')){
            $user->password = bcrypt($request->password);
        }

        
        if($request->has('type')){
            if(!$user->isVerified()){
                return $this->errorResponse('Unicamente los usuarios verificados pueden cambiar su valor de administrador', 409);
            }

            $user->type = $request->type;
        }


        

        if(!$user->isDirty()){
            return $this->errorResponse('Se debe especificar al menos un valor diferente para actualizar', 422);
        }

    

        $user->save();

        return $this->showOne($user, '', true);
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

    public function setCompany($user_id, $company_id){

        $user = User::select('id','current_company','type','verified','names','last_names')->findOrFail($user_id);

        $user->current_company = $company_id;

        $user->save();

        return $this->showOne($user,'',true);

    }

    function verify(Request $request){



        $data = $request->all();



        $user = User::where('verification_token',$data['verification_token'])
                    ->where('email',$data['email'])
                    ->firstOrFail();


        $user->verified = User::VERIFIED;
        $user->verification_token = '';
        $user->password = bcrypt($data['password']);

        
        $user->save();


        return $this->showMessage('Usuario verificado');

    }

    public function recoveryPssw($email){

        $user = User::where('email',$email)->first();
        
        if($user){

            $user->verification_token = User::genVerificationToken();
            Mail::to($user)->queue(new RecoveryUserPassword($user));
        }

        return $this->showMessage('Sucess');

    }

    public function newToken(User $user){


        $user->verified = User::NO_VERIFIED;
        $user->verification_token = User::genVerificationToken();

        $user->save();

        Mail::to($user)->queue(new newToken($user));    

        return $this->showOne($user,'',true);
    }

    public function userCompanies($id = null){

        if($id){

            $user = User::findOrFail($id);

            $companies = $user->companies;

            return $this->showAll($companies);

        }else{

            $user = Auth::user();

            if($user->type != "sadmin"){

            $companies = Company::all();

            }else{

                $companies = $user->companies;


            }

            return $this->showAll($companies);
        }

    }

    public function getUserByToken(){


        $user = Auth::user();

        return $this->showArray($user);

    }

    public function setCurrentCompany($company_id){

        $user = Auth::user();

        if($user->current_company != $company_id){

            $userDb = User::findOrFail($user->id);
            $userDb->current_company = $company_id;
            $userDb->save();
        }
        
        return $this->showMessage('');
    }

    public function isAdmin(){


        $user = Auth::user();

        if($user->type == User::SUPER_ADMIN){

            return $this->showMessage('',true);
        }


        return $this->showMessage('',false);
    }


}
