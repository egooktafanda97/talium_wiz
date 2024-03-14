<?php

namespace TaliumAbstract\Attributes;

use Attribute;

#[Attribute]
class Controllers
{
    public function __construct(public $controller = null)
    {
    }
}
