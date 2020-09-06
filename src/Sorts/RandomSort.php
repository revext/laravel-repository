<?php

namespace Revext\Repository\Sorts;

use Spatie\QueryBuilder\Sorts\Sort;
use Illuminate\Database\Eloquent\Builder;

class RandomSort implements Sort
{
    public function __invoke(Builder $query, bool $descending, string $property)
    {
        return $query->orderByRaw("RAND()");
    }
}
