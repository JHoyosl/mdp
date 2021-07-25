<?php

namespace App\Http\Controllers\User;

use App\Company;
use App\Http\Controllers\ApiController;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserSecurityController extends ApiController
{

    public function __construct(){

        $this->middleware('auth:api')->except(['verify','recoveryPssw']);

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

    public function addRol($name){

        $role = Role::create(['name' => strtoupper($name)]);

        return $role;
    }

    public function addPermission($name){

        $permission = Permission::create(['name' =>  strtoupper($name)]);

        return $permission;
    }

    public function getRoles(){

        $roles = Role::all();

        return $roles;
    }

    public function getPermission($rolName){

        $role = Role::where('name',$rolName)->with('permissions')->first();
        $permissions = Permission::all();

        return $this->showArray(["role" => $role, "permissions" => $permissions]);
    }

    public function getUserRoles($userId){

        $user = User::findOrFail($userId);

        $permissions = $user->getRoleNames();

        $roles = $this->getRoles();

        return $this->showArray(['asignados' => $permissions,'listado' => $roles]);

    }

    public function getUserList($search)
    {

        $user = Auth::user();

        $company = Company::findOrFail($user->current_company);

        switch ($user->type) {
            case User::SUPER_ADMIN:
                
                $users = User::where('email','like','%'.$search.'%')
                            ->orWhere('names','like','%'.$search.'%')
                            ->orWhere('last_names','like','%'.$search.'%')
                            ->get();
                return $this->showAll($users);

                break;

            case User::ADMIN:
            case User::USER:
                
                $users = $company->users;
                return $this->showAll($users);
                
                break;
            
            default:
                return $this->showAll([]);
                break;
        }
    }

    public function setUserRol(Request $request){

        $user = User::findOrFail($request->userId);
        $role = Role::findOrFail($request->rolId);

        $user->assignRole($role);
        
        return $user;
    }

    public function revokeUserRol(Request $request){
        
        $user = User::findOrFail($request->userId);
        $role = Role::findOrFail($request->rolId);

        $user->removeRole($role);
        
        return $user;
    }

    public function setRolPermission(Request $request){

        $permission = Permission::find($request->permissionId);
        $role = Role::find($request->rolId);
        $role->givePermissionTo($permission);

        return $role;

    }

    public function revokeRolPermission(Request $request){

        $permission = Permission::find($request->permissionId);
        $role = Role::find($request->rolId);
        $role->revokePermissionTo($permission);

        return $role;

    }



}
