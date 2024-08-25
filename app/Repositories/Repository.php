<?php

namespace App\Repositories;

use App\Interfaces\RepositoryInterface as BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class Repository implements BaseRepositoryInterface
{
    public function __construct(
        protected readonly Model $model
    )
    {
    }

    public function create(array $attributes): Model
    {
        return $this->model::factory()->create($attributes);
    }

    public function find(int $id): ?Model
    {
        return $this->model->find($id);
    }
}
