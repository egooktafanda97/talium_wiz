<?php

namespace TaliumAbstract\Attributes;

use Attribute;

#[Attribute]
class Controller
{
    public function __construct(public $controller = true)
    {
    }
}
