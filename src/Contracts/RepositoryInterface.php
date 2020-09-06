<?php

namespace Revext\Repository\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

interface RepositoryInterface {
    public function create($attributes = []): Model;

    public function update($id, $attributes = []): Model;

    public function updateAll($attributes = [], $columns = []): Collection;

    public function findById($id): Model;

    public function findByColumns($columns, $paginate = false, $limit = null);

    public function findFirst();

    public function findAll($paginate = false, $limit = null);
    
    public function delete($id);
    
    public function restore($id);
}