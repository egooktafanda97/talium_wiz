<?php

namespace TaliumAbstract\Attributes;

use Attribute;

#[Attribute]
class Args
{
    public function __construct(public $args = [])
    {

    }
}
