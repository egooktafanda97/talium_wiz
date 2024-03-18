<?php

namespace TaliumAbstract\Blueprint;

use Illuminate\Database\Eloquent\Model;
use PhpCsFixer\Config;
use PhpCsFixer\Console\ConfigurationResolver;
use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Runner;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\WhitespacesFixerConfig;

class ModelStub
{
    private string $namespace;
    private array $use_namespace = [];
    private array $classAttibute = [];
    private string $class;
    private array $trait = [];
    private array $property = [];

    private array $method = [];

    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;

    }

    public function setUseNamespace($namespace)
    {
        $this->use_namespace = $namespace;

    }

    public function setClassAttribute($attribute)
    {
        $this->classAttibute = $attribute;

    }

    public function setClass($class)
    {
        $this->class = $class;

    }

    public function setProperty($property)
    {
        $this->property = $property;

    }

    public function setTrait($trait)
    {
        $this->trait = $trait;

    }

    public function setMethod($method)
    {
        $this->method = $method;

    }

    public function extract()
    {
        return [
            'namespace' => $this->namespace ?? null,
            'use_namespace' => $this->use_namespace ?? null,
            'class' => $this->class ?? null,
            'classAttibute' => $this->classAttibute ?? null,
            'trait' => $this->trait ?? null,
            'property' => $this->property ?? null,
            'method' => $this->method ?? null,
        ];
    }

    public function get()
    {
        $data = $this->extract();
        $namespace = $data['namespace'];
        $use_namespace = $data['use_namespace'];
        $class = $data['class'];
        $classAttibute = $data['classAttibute'];
        $trait = $data['trait'];
        $property = $data['property'];
        $method = $data['method'];

        $use_namespace = implode("\n", array_map(function ($item) {
            return "use " . $item . ";";
        }, $use_namespace));

        $classAttibute = implode("\n", array_map(function ($item) {
            return $item;
        }, $classAttibute));

        $trait = implode("\n", array_map(function ($item) {
            return "use " . $item . ";";
        }, $trait));

        // Membuat string properti kelas
        $property = implode("\n", array_map(function ($key, $value) {
            if (is_array($value)) {
                $value = implode(", ", array_map(function ($v) {
                    return '"' . $v . '"';
                }, $value));
            } else if (is_string($value)) {
                $value = '"' . $value . '"';
            }
            return
                <<<EOF
    $key = [$value];
EOF;


        }, array_keys($property), $property));


        // Membuat string properti kelas
        $method = implode("\n", array_map(function ($key, $value) {
            $fin = $value['fn'];
            $attribute = implode(",", $value['attribute'] ?? []);
            return
                <<<EOF
            $attribute
            $key
            $fin
EOF;
        }, array_keys($method), $method));
        // Membangun string menggunakan EOF
        $output = <<<EOF
$use_namespace

$classAttibute
class $class extends Model
{

$trait
$property
$method
}
EOF;
        return $output;
    }
}
