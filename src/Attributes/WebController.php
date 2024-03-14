<?php

namespace TaliumAbstract\Attributes;

use Attribute;

#[Attribute]
class WebController
{
    public function __construct(public $controller = 'web')
    {
    }
}
