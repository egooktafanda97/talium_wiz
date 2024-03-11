<?php

namespace TaliumAbstract\Attributes\Ruters;

use Attribute;

#[Attribute]
class Name
{
    public function __construct(public $name)
    {
    }
}
