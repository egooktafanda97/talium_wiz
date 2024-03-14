<?php

namespace TaliumAbstract\Trait;

use TaliumAbstract\Attributes\Contract\ReflectionMeta;
use TaliumAbstract\Attributes\Table;

trait InjectModel
{
    public function Iject()
    {
        $this->fillable = collect(self::rules())->keys()->toArray();
        $this->table = ReflectionMeta::getAttribute($this, Table::class)->table;
    }
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->Iject();
    }
}
