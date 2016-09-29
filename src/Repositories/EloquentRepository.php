<?php

namespace Nwidart\Modules\Repositories;

use Closure;
use Illuminate\Support\Collection;
use Nwidart\Modules\Entities\Entity;
use Nwidart\Modules\Exceptions\Repositories\NoRecordsFound;
use Rinvex\Repository\Repositories\EloquentRepository as BaseEloquentRepository;

// TODO: implement updated RepositoryContract (returns collections and entities, or a boolean in case of delete)(interface should be source of truth as that is used for code completion)
class EloquentRepository extends BaseEloquentRepository
{
    use PerformsActions, HandlesMethodExtensions, ReturnsEntities;
    
    /**
     * Create a new entity with the given attributes.
     *
     * @param array $attributes
     *
     * @return Entity
     */
    public function create(array $attributes = []) : Entity
    {
        // TODO: should be able to pass an Entity as $attributes (break it down into an array by using get_object_vars() before passing on)(can't because method signature differs?)
        
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
        // TODO: should be able to pass an Entity as $attributes (break it down into an array by using get_object_vars() before passing on)
        
        return $this->performAction('update', $id, $attributes);
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
        // TODO: should be able to pass an Entity to delete ($id = $id instanceof Entity ? $id->id : $id)
        
        return $this->performAction('delete', $id);
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
            throw NoRecordsFound::emptyResult();
        }
        
        if ($result instanceof Collection && count($result) <= 0) {
            throw NoRecordsFound::emptyResultSet();
        }
        
        return $result;
    }
}