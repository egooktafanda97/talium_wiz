<?php

namespace TaliumAbstract\Permission;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleServices
{
    /*
     * Get Rules
     * @param $request
     * @return array
     * @throws \Exception
     */
    public function show($request)
    {
        return [
            "role" => Role::where('guard_name', $request->guard ?? 'web')->whereNotIn("name", ["super-admin"])->orderBy("id", "DESC")->get(),
            "permission" => Permission::whereNotNull("group_permission")
                ->where("guard_name", "web")
                ->when(!empty($request->get("group")), function ($q) use ($request) {
                    $q->where("group_permission", $request->get("group"));
                })
                ->get()
                ->map(function ($x) use ($request) {
                    $roles = Role::where("id", ($request->get("role-active") ?? null))->first();
                    $x->role_permissions = false;
                    if (!empty($roles) && $roles->hasPermissionTo($x->name))
                        $x->role_permissions = true;
                    return $x;
                }),
            "role_permission" => (Role::where("id", ($request->get("role-active") ?? null))->first()->permissions ?? []),
            "modules" => Permission::select(["group as group"])->groupBy("group_permission")->get()
        ];
    }

    /**
     * Save Rules
     * @param $request
     * @return array
     * @throws \Exception
     */
    public function saveRules($request)
    {
        try {
            if ($request->guard_api)
                Role::create(["name" => $request->role . "-api", "guard_name" => "api"]);
            Role::create(["name" => $request->role, "guard_name" => "web"]);
            return ["status" => true, "msg" => "Ok", "code" => 201];
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 403);
        }
    }

    /**
     * Save Permission
     * @param $rulesId
     * @param array $permissionId
     */
    public function saveRulePermission($rulesId, $permissionId = [])
    {
        try {
            $role = Role::find($rulesId);
            $permissions = Permission::whereIn("id", $permissionId)->pluck('id', 'id')->all();
            $role->syncPermissions($permissions);
            return ["status" => true, "msg" => "Ok", "code" => 201];
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 403);
        }
    }

    public function destory($name)
    {
        try {
            DB::table("roles")->where("name", $name)->delete();
            return ["status" => true, "code" => 201];
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }
    }


    /**
     * Set User Rules
     */
    public function setUserRules(Model $user, $rules_name)
    {
        try {
            $user->assignRole($rules_name);
            return ["status" => true, "msg" => "Ok", "code" => 200];
        } catch (\Exception $e) {
            return ["status" => false, "msg" => $e->getMessage(), "code" => 403];
        }
    }

    /**
     * Set User Permission
     */
    public function serUserPermission(Model $user, $permission_name)
    {
        try {
            $user->givePermissionTo($permission_name);
            return ["status" => true, "msg" => "Ok", "code" => 200];
        } catch (\Exception $e) {
            return ["status" => false, "msg" => $e->getMessage(), "code" => 403];
        }
    }

    /**
     * Set User Role Permission
     */
    public function setUserRolePermission(Model $user, $role_name, $permission_id, $guard_api = false)
    {
        try {
            $setter = ["api"];
            if ($guard_api)
                $setter = ["web", "api"];
            foreach ($setter as $set) {
                $role = Role::create(["name" => ($set == 'api' ? $role_name . "-api" : $role_name), "guard_name" => $set]);
                $permissions = Permission::whereIn("id", $permission_id)->pluck('id', 'id')->all();
                $role->syncPermissions($permissions);
                $user->assignRole($role->name);
            }
            return ["status" => true, "msg" => "Ok", "code" => 200];
        } catch (\Exception $e) {
            return ["status" => false, "msg" => $e->getMessage(), "code" => 403];
        }
    }

    /*
     * Get Role User
     * @param Model $user
     */
    public function getRoleUser(Model $user)
    {
        return [
            "user" => $user,
            "role" => $user->getRoleNames(),
            "permission" => $user->getAllPermissions()
        ];
    }
}
