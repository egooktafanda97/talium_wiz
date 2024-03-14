<?php

namespace TaliumAbstract\Attributes\Ruters;

use Attribute;

#[Attribute]
class Prefix
{
    public function __construct(public $prefix)
    {
    }
}
