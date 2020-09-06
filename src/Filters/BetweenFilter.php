<?php

namespace Revext\Repository\Filters;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class BetweenFilter implements Filter
{
    /**
     * @var array $seachColumns The columns to search in
     */
    protected $searchColumns;

    /**
     * @var array $forceAnd If true, then in case of array values it forces the search to use AND instead of OR
     */
    protected $forceAnd;

    public function __construct(array $searchColumns, $forceAnd = false)
    {
        $this->searchColumns = $searchColumns;
        $this->forceAnd = $forceAnd;
    }

    /**
     * Searches all the columns listed in $searchColumns. It looks for any partial solution.
     * It is capable of searching between two columns for one value or for a column between two values
     */
    public function __invoke(Builder $query, $value, string $property): Builder
    {
        $values = explode(':', $value);
        if(count($values) != 2){
            throw new \InvalidArgumentException();
        }

        $isFirstField = true;
        $searchColumns = $this->searchColumns;
        $query->where(function($query) use($isFirstField, $searchColumns, $values) {
            foreach ($this->searchColumns as $columnName) {
                try {
                    if(is_array($columnName) && count($columnName) == 2){
                        $this->addRangeBetweenColumnsToQuery($query, ($isFirstField || $this->forceAnd), $columnName, $values);
                    } elseif(is_string($columnName)) {
                        $this->addColumnBetweenRangeToQuery($query, ($isFirstField || $this->forceAnd), $columnName, $values);
                    }
                    $isFirstField = false;
                }catch (\Exception $e) {
                    throw $e;
                }
            }
        });
        return $query;
    }

    protected function addColumnBetweenRangeToQuery($query, $useAnd, $column, array $values){
        $whereHas = 'orWhereHas';
        $where = 'orWhereBetween';

        if ($useAnd) {
            $whereHas = 'whereHas';
            $where = 'whereBetween';
        }

        $relation = null;
        if (stripos($column, '.')) {
            $explode = explode('.', $column);
            $column = array_pop($explode);
            $relation = implode('.', $explode);
        }

        $modelTableName = $query->getModel()->getTable();

        if (!is_null($relation)) {
            $query->$whereHas($relation, function ($query) use ($column, $values) {
                return $query->whereBetween($column, $values);
            });
        } else {
            $query->$where($modelTableName . '.' . $column, $values);
        }
    }

    protected function addRangeBetweenColumnsToQuery($query, $useAnd, array $columns, array $values){
        $whereHas = 'orWhereHas';
        $where = 'orWhere';

        if ($useAnd) {
            $whereHas = 'whereHas';
            $where = 'where';
        }

        $modelTableName = $query->getModel()->getTable();

        $relation = null;
        $relation1 = null;
        if (stripos($columns[0], '.')) {
            $explode = explode('.', $columns[0]);
            $columns[0] = array_pop($explode);
            $relation1 = implode('.', $explode);
        }
        $relation2 = null;
        if (stripos($columns[1], '.')) {
            $explode = explode('.', $columns[1]);
            $columns[1] = array_pop($explode);
            $relation2 = implode('.', $explode);
        }

        if($relation1 !== $relation2){
            throw new \InvalidArgumentException();
        }
        $relation = $relation1;

        if (!is_null($relation)) {
            $query->$whereHas($relation, function ($query) use ($columns, $values) {
                return $query->where(function($query) use ($columns, $values) {
                    return $query->whereBetween($columns[0], [$values[0], $values[1]])
                        ->orWhereBetween($columns[1], [$values[0], $values[1]]);
                })->orWhere(function($query) use ($columns, $values) {
                    return $query->whereRaw('? between `' . $columns[0] . '` and `' .  $columns[1] . '`', [$values[0]])
                        ->orWhereRaw('? between `' . $columns[0] . '` and `' . $columns[1] . '`', [$values[1]]);
                });
            });
        } else {
            $query->$where(function($query)  use ($modelTableName, $columns, $values) {
                return $query->where(function($inner) use ($modelTableName, $columns, $values) {
                    return $inner->whereBetween($modelTableName . '.' .$columns[0], [$values[0], $values[1]])
                        ->orWhereBetween($modelTableName . '.' .$columns[1], [$values[0], $values[1]]);
                })->orWhere(function($inner) use ($modelTableName, $columns, $values) {
                    return $inner
                        ->whereRaw('? between `' . $modelTableName . '`.`' .$columns[0] . '` and `' . $modelTableName . '`.`' . $columns[1] . '`', [$values[0]])
                        ->orWhereRaw('? between `' . $modelTableName . '`.`' .$columns[0] . '` and `' . $modelTableName . '`.`' .$columns[1] . '`', [$values[1]]);
                });
            });
        }
    }
}
