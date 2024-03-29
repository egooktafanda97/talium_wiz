<?php

namespace TaliumAbstract\Attributes\Ruters;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use TaliumAbstract\Attributes\Contract\ReflectionMeta;

class RouterAttributeHandler
{
    public function __construct()
    {

    }

    public function attribute()
    {
    }

    public static function findController()
    {
        $appPath = app_path();
        $files = File::allFiles($appPath);
        $controllerFiles = [];

        foreach ($files as $file) {
            $filePath = $file->getPathname();
            $fileName = $file->getFilename();
            if (strpos($fileName, 'Controller.php') !== false) {
                $relativePath = str_replace($appPath . DIRECTORY_SEPARATOR, '', $filePath);
                $namespace = str_replace(DIRECTORY_SEPARATOR, '\\', $relativePath);
                $controllerNamespace = 'App\\' . substr($namespace, 0, -4);
                $controllerFiles[] = $controllerNamespace;
            }
        }
        return $controllerFiles;
    }

    public static function build($data): array
    {
        $routers = [];
        // Cek apakah kelas memiliki atribut dan jika memiliki kunci 'prefix'
        if (!empty($data['attribute'])) {
            // Iterasi setiap metode
            foreach ($data['methods'] as $method) {
                // Ambil informasi tentang metode
                $methodName = $method['method_name'];
                $attributes = $method['attributes'];

                // Buat router hanya jika metode memiliki atribut
                if (!empty($attributes)) {
                    // Inisialisasi array untuk menyimpan data router
                    $router = [];

                    // Setel metode HTTP berdasarkan atribut yang dimiliki metode
                    $url_method = '';
                    if (isset($attributes['Get'])) {
                        $router['method'] = 'GET';
                        $url_method = $attributes['Get'];
                    } elseif (isset($attributes['Post'])) {
                        $router['method'] = 'POST';
                        $url_method = $attributes['Post'];
                    } elseif (isset($attributes['Put'])) {
                        $router['method'] = 'PUT';
                        $url_method = $attributes['Put'];
                    } elseif (isset($attributes['Delete'])) {
                        $router['method'] = 'DELETE';
                        $url_method = $attributes['Delete'];
                    } else {
                        // Jika tidak ada atribut HTTP yang ditemukan, lewati metode ini
                        continue;
                    }
                    $router['prefix'] = $data['attribute']['prefix'] ?? $data['attribute']['group']['prefix'] ?? '';
                    $router['url'] = $url_method;
                    $router['controller'] = [$data['class'], $methodName];

                    // group
                    if (!empty($data['attribute']['group'])) {
                        $router['attribute_group'] = $data['attribute']['group'];
                    }


                    if (!empty($data['attribute']['group']['middleware'])) {
                        $router['middleware'] = $data['attribute']['middleware'];
                    }

                    // Tambahkan middleware ke router jika ada
                    if (!empty($data['attribute']['middleware'])) {
                        $router['middleware'] = $data['attribute']['middleware'];
                    }

                    // Tambahkan middleware ke router jika ada
                    if (!empty($data['attribute']['name'])) {
                        $router['name'] = $data['attribute']['name'];
                    }

                    if (!empty($attributes['name'])) {
                        $router['name'] .= "." . $attributes['name'];
                    } else {
                        $router['name'] = !empty($router['name']) ?
                            $router['name'] . "." . $methodName :
                            str_replace('/', '.', ($router['prefix'] . "/" . $method['method_name']));
                    }

                    $routers[] = $router;
                }
            }
        }
        return $routers;
    }

    public static function route()
    {
        $controllerFiles = self::findControllers();
        try {
            if (!empty(config('RouterAttributeNameSpace')) && is_array(config('RouterAttributeNameSpace')))
                $controllerFiles = config('RouterAttributeNameSpace');
            else
                $controllerFiles = self::findControllers();
        } catch (\Throwable) {
        }
        $rootPath = base_path();
        $controllerFiles = array_map(function ($path) use ($rootPath) {
            return str_replace($rootPath, '', $path);
        }, $controllerFiles);
        $routes_list = [];
        foreach ($controllerFiles as $key => $items) {
            foreach ($items as $item) {
                $routes = ReflectionMeta::HirarchyAttributes($item);
                if (!empty($routes['attribute'])) {
                    $arr = self::build((ReflectionMeta::HirarchyAttributes($item)));
                    foreach ($arr as $router) {
                        $routes_list[] = array_merge($router, ["guard" => $key ?? 'web']);
                    }
                }
            }
        }
        foreach ($routes_list as $router) {
            try {
                Route::group($router['guard'] === "api" ? ["api"] : [], function () use ($router) {
                    Route::group($router['attribute_group'] ?? [], function () use ($router) {
                        if (is_array($router['url'])) {
                            foreach ($router['url'] as $url) {
                                Route::group($router['method_group'] ?? [], function () use ($url, $router) {
                                    Route::{strtolower($router['method'])}($url, $router['controller'])
                                        ->name($router['name'] ?? null);
                                });
                            }
                        } else {
                            Route::{strtolower($router['method'])}($router['url'], $router['controller'])
                                ->name($router['name'] ?? null);
                        }
                    });
                });

            } catch (\Throwable) {
            }
        }
    }


    public static function test()
    {
        Route::post('/log', [\App\Http\Controllers\Api\Auth\LoginController::class, 'index']);
    }

    public static function findControllers()
    {
        $data = ReflectionMeta::findPhpFilesWithClass(app_path());
        $namespaces = [];
        foreach ($data as $classData) {
            $namespaces[$classData['controller']][] = $classData['namespace'];
        }
        return $namespaces;

    }

    public static function pushToConfig()
    {
        $data = self::findControllers();
        $namespaces = "";
        foreach ($data as $key => $classData) {
            $classes = collect($classData)->map(function ($x) {
                return $x . '::class';
            })->toArray();
            $namespaces .= "'" . $key . "' => [\n        " . implode(",\n        ", $classes) . "\n    ],\n";
        }

        $result = "<?php\n\nreturn [\n    " . $namespaces . "];\n";
        $configPath = config_path('RouterAttributeNameSpace.php');
        file_put_contents($configPath, $result);
    }
}
