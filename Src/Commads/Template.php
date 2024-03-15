<?php

namespace TaliumAbstract\Commads;

use App\Models\User;
use Touhidurabir\StubGenerator\Facades\StubGenerator;

trait Template
{

    public function tempalte_bind()
    {

        $htmls = '';
        foreach (User::createBlueprint() as $itms) {
            if ($itms['tag'] == 'input') {
                $htmls .= StubGenerator::from("templates/InputText.html")
                    ->withReplacers($itms)->toString();
            } else if ($itms['tag'] == 'text-area') {
                $htmls .= StubGenerator::from("templates/TextArea.html")
                    ->withReplacers($itms)->toString();
            } else if ($itms['tag'] == 'select') {
                $htmls .= StubGenerator::from("templates/Select.html")
                    ->withReplacers($itms)->toString();
            }
        }

        return $htmls;

    }

    public function handle()
    {
        $children = $this->tempalte_bind();
        $htmls = StubGenerator::from("templates/Container.html")
            ->withReplacers(["children" => $children])->toString();
        StubGenerator::from(__DIR__ . '/../stub/show.stub', true)
            ->to("resources/views")
            ->as('show.blade')
            ->withReplacers([
                "html" => '<div>' . $htmls . '</div>'
            ])
            ->replace(true)
            ->save();

    }
}
