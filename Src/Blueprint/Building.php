<?php

namespace TaliumAbstract\Blueprint;

use Touhidurabir\StubGenerator\Facades\StubGenerator;

class Building
{
    public function __construct(public $blueprint)
    {
    }

    public function model()
    {
        $data = $this->blueprint['Model'];
        $str = new ModelStub($data);
        $str->setNamespace('App\Models');
        $str->setUseNamespace($data['use']);
        $str->setClassAttribute($data['class']['attributes'] ?? []);
        $str->setTrait($data['traits'] ?? []);
        $str->setProperty(
            $data['properties'] ?? []
        );
        $str->setClass($data['class']['name'] ?? '');
        $str->setMethod(
            $data['methods'] ?? []
        );
        StubGenerator::from(__DIR__ . '/stub/Model/main.stub', true)
            ->to(app_path('Models'))
            ->as($data['class']['name'])
            ->withReplacers([
                "content" => $str->get()
            ])
            ->replace(true)->save();
        $filePath = public_path("TestModel.php");
        exec("php vendor/bin/php-cs-fixer fix $filePath", $output, $returnVar);
        return $this;
    }

    public function blade()
    {
        $data = $this->blueprint['Blade'];
        $str = (new BladeStub($data))->Identify();
        return $this;
    }

}
