<?php

namespace TaliumAbstract\Attributes\Ruters;

use Attribute;

#[Attribute]
class Get
{
    public function __construct(public $get)
    {
    }
}
