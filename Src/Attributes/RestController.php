<?php

namespace TaliumAbstract\Attributes;

use Attribute;

#[Attribute]
class RestController
{
    public function __construct(public $controller = 'api')
    {
    }
}
