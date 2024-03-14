<?php

namespace TaliumAbstract\Attributes;

use Attribute;

#[Attribute]
class Model
{
    public function __construct(public $model)
    {
    }
}
