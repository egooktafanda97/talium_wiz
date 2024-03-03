<?php

namespace TaliumAbstract\Services;

use Exception;
use TaliumAbstract\Repository\BaseRepository;

class AutoCrudApiServices
{

    private $repository;
    public function __construct(public $parent, public $model)
    {
        $this->repository = new BaseRepository($this->model);
    }
    public function get()
    {
        return $this->repository->all();
    }

    public function paginate(int $limit = null, int $page = 1)
    {
        return $this->repository->builder()
            ->orderBy($this->model->getModelTable() . '.created_at', 'desc')
            ->paginate($limit ?? 25, $page);
    }

    public function find($id)
    {
        return  $this->repository->find($id);
    }


    public function store($request)
    {
        try {
            $validatedData = collect($request->validated());
            if (!empty($this->parent->apped_save))
                $validatedData->merge($this->parent->apped_save);
            $model = $this->repository->create($validatedData->toArray());
            if (!$model) {
                throw new \Exception("Error saving data");
            }
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
        return $model;
    }



    public function update($request, $id)
    {
        try {
            $validatedData = collect($request->validated());
            if (!empty($this->parent->apped_update))
                $validatedData->merge($this->parent->apped_update);
            $model = $this->repository->update($validatedData->toArray(), $id);
            if (!$model) {
                throw new \Exception("Error update data");
            }
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
        return $model;
    }

    public function destory($id)
    {
        try {
            $model = $this->model->whereId($id)->first();
            if (!$model)
                throw new \Exception("key not found!");
            $this->repository->delete($id);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
        return $model;
    }
}
