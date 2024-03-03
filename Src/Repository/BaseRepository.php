<?php

namespace TaliumAbstract\Repository;

use Lerouse\LaravelRepository\EloquentRepository;
use Illuminate\Database\Eloquent\Builder;

class BaseRepository extends EloquentRepository
{
    public function __construct(public $model)
    {
    }

    public function builder(): Builder
    {
        return $this->model::query();
    }
}
