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

    public static function register_permissions_route()
    {
        $routes = Route::getRoutes()->getRoutes();
        foreach ($routes as $route) {
            if ($route->getName() != '' && !empty($route->getName())) {
                $permission = Permission::whereName($route->getName())->first();
                if (is_null($permission)) {
                    if ((($route->getAction()['middleware']['0'] ?? null) === 'web' || ($route->getAction()['middleware']['0'] ?? null) === 'api'))
                        Permission::create(['name' => $route->getName(), "guard_name" => ($route->getAction()['middleware']['0'] ?? null), "group" => $route->getAction()['group'] ?? null]);
                    else
                        Permission::create(['name' => $route->getName(), "guard_name" => 'web', "group" => $route->getAction()['group'] ?? null]);
                }
            }
        }
    }

}
