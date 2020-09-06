<?php

namespace Revext\Repository\Utils\Repositories;

use Revext\Repository\Repository;
use Spatie\QueryBuilder\AllowedFilter;
use Revext\Repository\Utils\Models\Person;
use Revext\Repository\Filters\SearchFilter;

class PersonRepository extends Repository{

    protected $modelClass = Person::class;
    
    /**
     * @var array
     */
    protected $allowedFilters = [
        'name'
    ];

    protected $allowedAppends = [
        'namail'
    ];

    public function boot(){
        $this->allowedFilters[] = AllowedFilter::custom('search', new SearchFilter(['name']));
    }
}