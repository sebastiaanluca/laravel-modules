<?php

namespace Nwidart\Modules\Repositories;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Nwidart\Modules\Entities\Entity;
use Nwidart\Modules\Exceptions\Repositories\NoRecordsFound;
use Rinvex\Repository\Repositories\EloquentRepository as BaseEloquentRepository;

// TODO: implement updated RepositoryContract (returns collections and entities, or a boolean in case of delete)(interface should be source of truth as that is used for code completion)
class EloquentRepository extends BaseEloquentRepository
{
    use PerformsActions, HandlesMethodExtensions, ReturnsEntities;
    
    /**
     * @var bool
     */
    protected $preventCallbackExecution;
    
    /**
     * Create a new entity with the given attributes.
     *
     * @param array $attributes
     *
     * @return Entity
     */
    public function create(array $attributes = []) : Entity
    {
        // TODO: should be able to pass an Entity as $attributes (break it down into an array by using get_object_vars() before passing on)(can't because method signature differs?) (new method "save" or "createFromEntity")
        
        return $this->performAction('create', $attributes);
    }
    
    /**
     * Update an entity with the given attributes.
     *
     * @param mixed $id
     * @param array $attributes
     *
     * @return Entity
     */
    public function update($id, array $attributes = []) : Entity
    {
        $instance = $this->getRawRecord($id);
        
        // Take attribute values from the entity itself
        // unless selective attributes are specified
        if ($id instanceof Entity && empty($attributes)) {
            $attributes = get_object_vars($id);
        }
        
        $result = $this->performAction('update', $instance, $attributes);
        
        return $result;
    }
    
    /**
     * Delete an entity with the given id.
     *
     * @param mixed $id
     *
     * @return bool
     */
    public function delete($id) : bool
    {
        $instance = $this->getRawRecord($id);
        
        return $this->performAction('delete', $instance);
    }
    
    /**
     * Delete all or a subset of records.
     *
     * Great to use with wheres to destroy a whole set of records at once.
     *
     * @return int The number of deleted records.
     */
    public function deleteAll() : int
    {
        return $this->executeCallback(get_called_class(), __FUNCTION__, func_get_args(), function() {
            return $this->prepareQuery($this->createModel())->delete();
        });
    }
    
    /**
     * Find all entities.
     *
     * @param array $attributes
     *
     * @return \Illuminate\Support\Collection
     */
    public function get($attributes = ['*'])
    {
        return $this->executeCallback(get_called_class(), __FUNCTION__, func_get_args(), function() use ($attributes) {
            return $this->prepareQuery($this->createModel())->get($attributes);
        });
    }
    
    /**
     * Dynamically pass missing methods to the model.
     *
     * @param string $method
     * @param array $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->executeCallback(get_called_class(), __FUNCTION__, func_get_args(), function() use ($method, $parameters) {
            return call_user_func_array(['parent', $method], $parameters);
        });
    }
    
    /**
     * Get the dynamically handled method extensions.
     *
     * @return array
     */
    protected function getMethodExtensions() : array
    {
        return [
            'OrFail' => [$this, 'validateResult'],
        ];
    }
    
    /**
     * @param integer|string|\Nwidart\Modules\Entities\Entity $id
     *
     * @return \Illuminate\Database\Eloquent\Model
     * @throws \Exception
     */
    protected function getRawRecord($id) : Model
    {
        // Perform the following actions and searches without caching or conversions
        $this->preventCallbackExecution = true;
        
        $id = $id instanceof Entity ? $id->getKeyValue() : $id;
        
        // Prevent passing an array to find() as it will return a collection and break the flow
        if (! is_string($id) && ! is_int($id)) {
            throw new \Exception('The given id should be a string or an integer.');
        }
        
        $raw = $this->find($id);
        
        $this->preventCallbackExecution = false;
        
        return $raw;
    }
    
    /**
     * Execute given callback and return the result.
     *
     * @param string $class
     * @param string $method
     * @param array $args
     * @param \Closure $closure
     *
     * @return mixed
     */
    protected function executeCallback($class, $method, $args, Closure $closure)
    {
        // Prevent any conversions and caching when the repository
        // is performing an internal search or action
        if ($this->preventCallbackExecution) {
            $result = call_user_func($closure);
            
            $this->resetRepository();
            
            return $result;
        }
        
        // Wrap in another closure to cache the converted entity and
        // not convert after first caching the original database result
        return parent::executeCallback($class, $method, $args, function() use ($closure) {
            return $this->convertToEntityResult(call_user_func($closure));
        });
    }
    
    /**
     * Throw exceptions if a result is not valid.
     *
     * @param mixed $result
     * @param string $method
     *
     * @return mixed
     */
    protected function validateResult($result, string $method)
    {
        if (is_null($result)) {
            throw NoRecordsFound::emptyResult($this->entity);
        }
        
        if ($result instanceof Collection && count($result) <= 0) {
            throw NoRecordsFound::emptyResultSet($this->entity);
        }
        
        return $result;
    }
}