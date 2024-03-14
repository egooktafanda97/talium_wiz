<?php

namespace TaliumAbstract\Attributes\Ruters;

use Attribute;

#[Attribute]
class Url
{
    public function __construct(public $url)
    {
    }
}
