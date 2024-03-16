<?php

namespace TaliumAbstract\Providers;

use TaliumAbstract\Attributes\Args;
use TaliumAbstract\Attributes\Model;
use TaliumAbstract\Trait\UseAttributeParameter;

class ModelBlueprint
{
    public function __construct(public $blueprint)
    {
    }

    public function extract()
    {
        return $this->blueprint;
    }
}
