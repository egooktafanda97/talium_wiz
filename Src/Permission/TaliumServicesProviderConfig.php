<?php

namespace TaliumAbstract\Permission;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;

class TaliumServicesProviderConfig
{
    public static function boot()
    {
        Gate::before(function ($user, $ability) {
            return $user->hasRole('root') ? true : null;
        });
    }

    public static function push_permissions()
    {
        $routes = Route::getRoutes()->getRoutes();
        foreach ($routes as $route) {
            if ($route->getName() != '') {
                $permission = Permission::where('name', $route->getName())->first();
                if (is_null($permission)) {
                    if ((($route->getAction()['middleware']['0'] ?? null) == 'web' || ($route->getAction()['middleware']['0'] ?? null) == 'api'))
                        Permission::create(['name' => $route->getName(), "guard_name" => ($route->getAction()['middleware']['0'] ?? null), "group_permission" => $route->getAction()['group'] ?? null]);
                    else
                        Permission::create(['name' => $route->getName(), "guard_name" => 'web', "group_permission" => $route->getAction()['group'] ?? null]);
                }
//                else {
//                    $permission->update(['name' => $route->getName(), "guard_name" => ($route->getAction()['middleware']['0'] ?? null), "group_permission" => $route->getAction()['group'] ?? null]);
//                    $permission->save();
//                }
            }
        }
    }

}
