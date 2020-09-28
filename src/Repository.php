<?php 

namespace Revext\Repository;

use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Container\Container as Application;
use Revext\Repository\Contracts\RepositoryInterface;
use Revext\Repository\Exceptions\RepositoryException;

abstract class Repository implements RepositoryInterface {

    /**
     * The laravel application variable
     * 
     * @var Application
     */
    protected $app = null;

    /**
     * Spatie QueryBuilder, for more info check https://docs.spatie.be/laravel-query-builder/v2/introduction/
     * 
     * @var QueryBuilder $query
     */
    protected $query = null;

    /**
     * The model class, overwrite in child classes
     * 
     * @var string $modelClass
     */
    protected $modelClass = null;

    
    /**
     * The model object, which is the base for the query
     * 
     * @var Model $model
     */
    protected $model = null;

    
    /**
     * The request from which the data is being read, if null then it used the app given request object
     * 
     * @var Request $request
     */
    protected $request = null;

    /**
     * Spatie QueryBuilder related appends
     * 
     * @var array $allowedAppends
     */
    protected $allowedAppends = [];

    /**
     * Spatie QueryBuilder related filters
     * 
     * @var array $allowedFilters
     */
    protected $allowedFilters = [];

    /**
     * Spatie QueryBuilder related fields
     * 
     * @var array $allowedFields
     */
    protected $allowedFields = [];

    /**
     * Spatie QueryBuilder related includes
     * 
     * @var array $allowedIncludes
     */
    protected $allowedIncludes = [];

    /**
     * Spatie QueryBuilder related sorts
     * 
     * @var array $allowedSorts
     */
    protected $allowedSorts = [];
    
    /**
     * Final constructor, for any initialization use the boot method
     */
    final public function __construct(Application $app){
        $this->app = $app;
        $this->makeQuery();

        $this->boot();
    }

    /**
     * Initialize any dependencies in the method
     */
    abstract function boot();

    /**
     * Resets the QueryBuilder
     */
    public function resetQuery(){
        return $this->makeQuery(true);
    }

    /**
     * Initializes the query builder, with the first param you can reset the builder
     * 
     * @var bool $forceNew Forces a new query builder
     */
    public function makeQuery($forceNew = false){
        if($this->query === null || $forceNew){
            if(!$this->model) {
                $this->model = new $this->modelClass;
            }
            $this->query = QueryBuilder::for($this->model->query(), $this->request ?? request());
        }

        return $this;
    }


    /**
     * Sets the base mode from which the query is made from
     * 
     * @var Model $model The model to base the query on
     */
    public function setModel(Model $model){
        $this->model = $model;

        return $this;
    }
    
    /**
     * Sets the request to read the data from
     * 
     * @var Request $request The request object
     */
    public function setRequest(Request $request){
        $this->request = $request;

        return $this;
    }

    /**
     * Creates the related model
     */
    public function create($attributes = []): Model {
        $entity = $this->query->create($attributes);
        
        $this->resetQuery();

        return $entity->refresh();
    }

    public function update($id, $attributes = []): Model{
        $entity = $this->query->findOrFail($id);

        $entity->fill($attributes);

        $entity->save();

        $this->resetQuery();

        return $entity->refresh();
    }

    public function updateAll($attributes = [], $columns = []): Collection{
        $this->query->where($attributes)->update($columns);

        $result = $this->findByColumns($columns);

        $this->resetQuery();

        return $result;
    }

    public function findById($id): Model{
        $this->applyCriteria();
        
        $result = $this->query->findOrFail($id);

        $this->resetQuery();

        return $result;
    }

    public function findByColumns($columns, $paginate = false, $limit = null){
        $this->applyCriteria();

        $this->query->where($columns);
        $result = $this->getResults($paginate, $limit);
        
        $this->resetQuery();

        return $result;
    }

    public function findFirst(){

        $result = $this->query->first();

        $this->resetQuery();

        return $result;
    }

    public function findAll($paginate = false, $limit = null){
        $this->applyCriteria();

        $result = $this->getResults($paginate, $limit);

        $this->resetQuery();

        return $result;
    }

    public function delete($id){
        $entity = $this->findById($id);
        
        if($this->hasTrait('Illuminate\Database\Eloquent\SoftDeletes') && $this->hasTrait('Revext\Repository\Traits\HasStatusAttribute')){
            $statusAttribute = $this->modelClass::getStatusAttribute();
            if (in_array($statusAttribute, $entity->getAttributes())) {
                $entity->$statusAttribute = $this->modelClass::getDeletedStatusValue();
                $entity->save();
            }
        }

        $entity->delete();

        return $entity;
    }

    public function restore($id){
        if(!$this->hasTrait('Illuminate\Database\Eloquent\SoftDeletes')){
            throw new RepositoryException("The model doesn't uses the SoftDeletes trait! The model cannot be restored!");
        }

        $entity = $this->query->withTrashed()->find($id);

        if($this->hasTrait('Revext\Repository\Traits\HasStatusAttribute')){
            $statusAttribute = $this->modelClass::getStatusAttribute();
            if (in_array($statusAttribute, $entity->getAttributes())) {
                $entity->$statusAttribute = $this->modelClass::getRestoredStatusValue();
                $entity->save();
            }
        }
        
        $entity->restore();

        return $entity;
    }

    public function scopeQuery($function){
        $this->query = $function($this->query);

        return $this;
    }

    private function applyCriteria(){
        if(count($this->allowedAppends)){
            $this->query->allowedAppends($this->allowedAppends);
        }
        if(count($this->allowedFields)){
            $this->query->allowedFields($this->allowedFields);
        }
        if(count($this->allowedFilters)){
            $this->query->allowedFilters($this->allowedFilters);
        }
        if(count($this->allowedSorts)){
            $this->query->allowedSorts($this->allowedSorts);
        }
        if(count($this->allowedIncludes)){
            $this->query->allowedIncludes($this->allowedIncludes);
        }
    }

    private function getResults($paginate = false, $limit = null){
        if($paginate){
            return $this->query->paginate($limit ?? config('repository.pagination.limit'));
        }

        return $this->query->get();
    }

    private function hasTrait($traitName): bool{
        return in_array($traitName, class_uses($this->modelClass));
    }
}