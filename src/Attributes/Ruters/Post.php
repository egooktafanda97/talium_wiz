<?php

namespace TaliumAbstract\Attributes\Ruters;

use Attribute;

#[Attribute]
class Post
{
    public function __construct(public $post)
    {
    }
}
