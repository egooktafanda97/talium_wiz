<?php

namespace TaliumAbstract\Permission;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class  PermissionServices
{

    public function __construct()
    {
        //
    }

    public function setRules($request)
    {
        $role = Role::create(['name' => $request->get('name')]);
        $role->syncPermissions($request->get('permission'));
    }

    public function updateRules(Role $role, Request $request)
    {
        $role->update($request->only('name'));
        $role->syncPermissions($request->get('permission'));

    }

    public function setPermission()
    {

    }

}
