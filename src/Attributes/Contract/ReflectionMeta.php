<?php

namespace TaliumAbstract\Attributes\Contract;

use Symfony\Component\Finder\Finder;
use ReflectionClass;
use ReflectionMethod;
use TaliumAbstract\Attributes\Args;
use TaliumAbstract\Attributes\Controllers;
use TaliumAbstract\Attributes\RestController;
use TaliumAbstract\Attributes\Ruters\Delete;
use TaliumAbstract\Attributes\Ruters\Get;
use TaliumAbstract\Attributes\Ruters\Group;
use TaliumAbstract\Attributes\Ruters\Middleware;
use TaliumAbstract\Attributes\Ruters\Name;
use TaliumAbstract\Attributes\Ruters\Post;
use TaliumAbstract\Attributes\Ruters\Prefix;
use TaliumAbstract\Attributes\Ruters\Put;
use TaliumAbstract\Attributes\WebController;

class ReflectionMeta
{
    /**
     * mencari attribute tertentu di dalam class
     * @param string $directory
     * @return array
     * @throws \ReflectionException
     */
    public static function getAttribute($class, $attributeClassName, $args = null)
    {
        $reflectionClass = new ReflectionClass($class);
        $classAttributes = $reflectionClass->getAttributes($attributeClassName);
        foreach ($classAttributes as $classAttribute) {
            $attributeInstance = $classAttribute->newInstance();
            if (!empty($args) && property_exists($attributeInstance, $args)) {
                return $attributeInstance->$args;
            }
            return $attributeInstance;
        }
        return null;
    }

    /**
     * membuat hierarki atribut router
     * @param string $directory
     * @return array
     * @throws \ReflectionException
     */
    public static function HirarchyAttributes($class)
    {
        $className = $class;

        $class = new ReflectionClass($className);

        // Mendapatkan nama kelas
        $className = $class->getName();


        // Inisialisasi array untuk menyimpan data
        $data = [
            "class" => $className,
            "attribute" => [],
            "methods" => []
        ];

        // Mendapatkan atribut Prefix dari kelas jika ada
        $prefixAttribute = $class->getAttributes(Prefix::class);
        if (!empty($prefixAttribute)) {
            $prefix = $prefixAttribute[0]->newInstance()->prefix;
            $data['attribute']['prefix'] = $prefix;
        }

        // Mendapatkan atribut Group dari kelas jika ada
        $groupAttribute = $class->getAttributes(Group::class);
        if (!empty($groupAttribute)) {
            $group = $groupAttribute[0]->newInstance()->group;
            $data['attribute']['group'] = $group;
        }


        // Mendapatkan atribut Group dari kelas jika ada
        $groupAttribute = $class->getAttributes(Name::class);
        if (!empty($groupAttribute)) {
            $name = $groupAttribute[0]->newInstance()->name;
            $data['attribute']['name'] = $name;
        }


        // Mendapatkan semua metode dalam kelas
        $methods = $class->getMethods();

        // Iterasi semua metode
        $i = 1;
        foreach ($methods as $method) {
            // Mendapatkan nama metode
            $methodName = $method->getName();

            // Inisialisasi array untuk menyimpan data metode
            $methodData = [
                "method_name" => $methodName,
                "attributes" => []
            ];

            // Mendapatkan atribut Get dari metode jika ada
            $getAttribute = $method->getAttributes(Get::class);
            if (!empty($getAttribute)) {
                $methodData['attributes']['Get'] = $getAttribute[0]->newInstance()->get;
            }

            // Mendapatkan atribut Delete dari metode jika ada
            $deleteAttribute = $method->getAttributes(Delete::class);
            if (!empty($deleteAttribute)) {
                $methodData['attributes']['Delete'] = $deleteAttribute[0]->newInstance()->delete;
            }

            // Mendapatkan atribut Post dari metode jika ada
            $postAttribute = $method->getAttributes(Post::class);

            if (!empty($postAttribute)) {
                $methodData['attributes']['Post'] = $postAttribute[0]->newInstance()->post;
            }

            // Mendapatkan atribut Put dari metode jika ada
            $putAttribute = $method->getAttributes(Put::class);
            if (!empty($putAttribute)) {
                $methodData['attributes']['Put'] = $putAttribute[0]->newInstance()->put;
            }

            // Mendapatkan atribut Middleware dari metode jika ada
            $middlewareAttribute = $method->getAttributes(Middleware::class);
            if (!empty($middlewareAttribute)) {
                $middleware = $middlewareAttribute[0]->newInstance()->middleware;
                $methodData['attributes']['Middleware'] = $middleware;
            }

            $nameMothodAttr = $method->getAttributes(Name::class);
            if (!empty($nameMothodAttr)) {
                $name = $nameMothodAttr[0]->newInstance()->name;
                $methodData['attributes']['name'] = $name;
            }

            // Mendapatkan atribut Name dari metode jika ada
            $nameAttribute = $method->getAttributes(Name::class);
            if (!empty($nameAttribute)) {
                $name = $nameAttribute[0]->newInstance()->name;
                $methodData['attributes']['name'] = $name;
            }
            if (!$methodData['attributes'])
                continue;
            $data['methods'][] = $methodData;
        }
        return $data;
    }


    /**
     * Get all PHP files with class and namespace
     * @param string $directory
     * @return array
     * @throws \ReflectionException
     * Mencari semua file PHP dengan dengan attribute controller, web, api
     */
    public static function findPhpFilesWithClass($directory)
    {
        $classesWithNamespace = [];

        // Recursive function to search for PHP files with class and namespace
        $searchInDirectory = function ($dir, $namespace = '') use (&$searchInDirectory, &$classesWithNamespace) {
            $files = scandir($dir);
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $path = $dir . '/' . $file;
                if (is_dir($path)) {
                    $subNamespace = $namespace !== '' ? $namespace . '\\' . $file : $file;
                    $searchInDirectory($path, $subNamespace);
                } elseif (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
                    // Read PHP file contents
                    $content = file_get_contents($path);
                    // Check if the file contains a namespace and class
                    if (preg_match('/\bnamespace\s+([\w\\\]+);.*?\bclass\s+(\w+)/s', $content, $matches)) {
                        $namespace = isset($matches[1]) ? $matches[1] : '';
                        $class = isset($matches[2]) ? $matches[2] : '';
                        $namespaces = $namespace . '\\' . $class;
                        $class = new ReflectionClass($namespaces);
                        $className = $class->getName();
                        $ClassControll = $class->getAttributes(Controllers::class);
                        if (!empty($ClassControll)) {
                            $controllers = $ClassControll[0]->newInstance()->controller;
                            $data = [
                                "controller" => $controllers ?? 'web',
                                "namespace" => $className,
                                "attribute" => $controllers
                            ];
                            $classesWithNamespace[] = $data;
                        }
                        $ClassControll = $class->getAttributes(RestController::class);
                        if (!empty($ClassControll)) {
                            $controllers = $ClassControll[0]->newInstance()->controller;
                            $data = [
                                "controller" => "api",
                                "namespace" => $className,
                                "attribute" => $controllers
                            ];
                            $classesWithNamespace[] = $data;
                        }
                        $ClassControll = $class->getAttributes(WebController::class);
                        if (!empty($ClassControll)) {
                            $controllers = $ClassControll[0]->newInstance()->controller;
                            $data = [
                                "controller" => "web",
                                "namespace" => $className,
                                "attribute" => $controllers
                            ];
                            $classesWithNamespace[] = $data;
                        }

                    }
                }
            }
        };
        $searchInDirectory($directory);
        return $classesWithNamespace;
    }

    /**
     * mengambil value dari attribute parameter args
     */
    public static function getAttributeArgs($class, $method)
    {
        $reflection = new ReflectionMethod($class, $method);

        dd($parameters = $reflection->getParameters());
        $parameters = $reflection->getParameters();
        if (empty($parameters)) {
            return null;
        }
        dd($reflection);
        foreach ($parameters as $parameter) {
            $attributes = $parameter->getAttributes();
            foreach ($attributes as $attribute) {
                return $attribute->getArguments();
            }
        }
    }
}
