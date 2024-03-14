<?php

namespace TaliumAbstract\Attributes\Ruters;

use Attribute;

#[Attribute]
class Put
{
    public function __construct(public $put)
    {
    }
}
