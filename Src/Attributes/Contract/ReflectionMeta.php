<?php

namespace TaliumAbstract\Attributes\Contract;

use ReflectionClass;
use ReflectionMethod;
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

    public static function HirarchyAttributes($class)
    {
        $className = $class;
        $class = new ReflectionClass($className);

        // Mendapatkan nama kelas
        $className = $class->getName();
        $ClassControll = $class->getAttributes(Controllers::class);
        if (empty($ClassControll)) {
            return [];
        }

        $RestclassControll = $class->getAttributes(RestController::class);
        if (!empty($RestclassControll)) {
            return [];
        }

        $WebClassControll = $class->getAttributes(WebController::class);
        if (!empty($WebClassControll)) {
            return [];
        }

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
}
