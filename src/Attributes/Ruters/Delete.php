<?php

namespace TaliumAbstract\Attributes\Ruters;

use Attribute;

#[Attribute]
class Delete
{
    public function __construct(public $delete)
    {
    }
}
