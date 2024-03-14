<?php

namespace TaliumAbstract\Attributes\Ruters;

use Attribute;

#[Attribute]
class Middleware
{
    public function __construct(public $middleware)
    {
    }
}
