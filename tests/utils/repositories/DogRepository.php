<?php

namespace Revext\Repository\Utils\Repositories;

use Revext\Repository\Repository;
use Revext\Repository\Utils\Models\Dog;

class DogRepository extends Repository{

    protected $modelClass = Dog::class;

    public function boot(){
        
    }
}