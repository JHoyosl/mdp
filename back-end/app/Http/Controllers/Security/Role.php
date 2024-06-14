<?php

namespace App\Http\Controllers\Security;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role as RoleModel;
use App\Http\Controllers\Controller;
use Exception;

class Role extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return RoleModel::all();
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'guard_name' => 'required'
        ]);

        $name = $request->name;
        $guard = $request->guard;

        $role = RoleModel::create([
            'name' => $name,
            'guard_name' => $guard
        ]);

        return $role;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(RoleModel $role)
    {
        return $role;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(RoleModel $role)
    {
        return "must be implemented";
    }
}
