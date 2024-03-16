<?php

namespace TaliumAbstract\Providers;

use Illuminate\Support\Facades\File;
use Symfony\Component\Yaml\Yaml;
use TaliumAbstract\Attributes\Args;

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
        return $this->blueprints;
    }

    public function getBlueprint()
    {
        return $this->blueprint;
    }
}
