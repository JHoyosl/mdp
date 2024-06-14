<?php

namespace App\Http\Controllers\Security;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;

class RolePermission extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Role $role)
    {
        return $role->with('permissions')->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Role $role)
    {
        $request->validate([
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'exists:permissions,id'
        ]);

        return $role->syncPermissions($request->permissions);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Role $role)
    {
        $request->validate([
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'exists:permissions,id'
        ]);

        return $role->revokePermissionTo($request->permissions);
    }
}
