<?php

namespace TaliumAbstract\Blueprint;

use Illuminate\Support\Facades\File;
use Symfony\Component\Yaml\Yaml;

class  BlueprintYmal
{
    private $blueprints;

    public function __construct(public $blueprint)
    {

    }

    public function extract()
    {
        $pathToFile = $this->blueprint;
        if (File::exists($pathToFile)) {
            $fileContents = File::get($pathToFile);
            $yamlData = Yaml::parse($fileContents);
            $this->blueprints = $yamlData['Blueprint'];
        } else {
            throw new \Exception("File not found");
        }
        return $this;
    }

    public function Model()
    {
        (new Building($this->blueprints))
            ->model();
    }

    public function Blade()
    {
        (new Building($this->blueprints))
            ->blade();
    }

    public function run($args)
    {
        foreach ($args as $arg) {
            $this->$arg();
        }
    }

    public function getBlueprint()
    {
        return $this->blueprint;
    }
}
