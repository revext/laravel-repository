<?php

namespace Revext\Repository\Filters;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class SearchFilter implements Filter
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
     */
    public function __invoke(Builder $query, $value, string $property): Builder
    {
        return $query->where(function($inner) use($value, $property){
            $isFirstField = true;

            if(is_array($value)){
                $value = implode(config('repository.filters.search.separator', ','), $value);
            }
            foreach ($this->searchColumns as $columnName) {
                try {
                    $value = "%{$value}%";
                    $this->addToQuery($inner, ($isFirstField || $this->forceAnd), $columnName, $value);
                    $isFirstField = false;
                }catch (\Exception $e) {
                }
            }
            return $inner;
        });
    }

    protected function addToQuery($query, $useAnd, $column, $value) {
        $whereHas = 'orWhereHas';
        $where = 'orWhere';

        if ($useAnd) {
            $whereHas = 'whereHas';
            $where = 'where';
        }

        $relation = null;
        if (stripos($column, '.')) {
            $explode = explode('.', $column);
            $column = array_pop($explode);
            $relation = implode('.', $explode);
        }

        $modelTableName = $query->getModel()->getTable();

        if (!is_null($value)) {
            if (!is_null($relation)) {
                $query->$whereHas($relation, function ($query) use ($column, $value) {
                    $query->where($column, 'LIKE', $value);
                });
            }else {
                $query->$where($modelTableName . '.' . $column, 'LIKE', $value);
            }
        }
    }
}
