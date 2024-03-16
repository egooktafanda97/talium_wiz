<?php

namespace TaliumAbstract\Trait;

use TaliumAbstract\Attributes\ArgumentAttribute\Handler;

trait UseAttributeParameter
{
    private $attributes;

    public function setAttribute($attr)
    {
        $this->attributes = $attr;
    }

    public function __construct()
    {
        $this->useAttributes();
    }

    public function useAttributes()
    {
        $trace = debug_backtrace();
        $this->setAttribute((new Handler(basename(__CLASS__), $trace))->attributes());
    }

    /**
     * struktur 2
     */

    public function argumetAttribute($class)
    {
        return $this->attributes->getArgument($class);
    }
}
