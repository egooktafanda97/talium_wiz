<?php

namespace TaliumAbstract\Attributes\Ruters;

use Attribute;

#[Attribute]
class Group
{
    public function __construct(public $group)
    {
    }
}
