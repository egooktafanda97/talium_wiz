<?php

namespace TaliumAbstract\Attributes;

use Attribute;

#[Attribute]
class Propertis
{
    public function __construct(public $props)
    {
    }
}
