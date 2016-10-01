<?php

namespace Nwidart\Modules\Repositories;

use Illuminate\Support\Collection;
use Nwidart\Modules\Entities\Entity;
use Nwidart\Modules\Factories\EntityFactory;

trait ReturnsEntities
{
    /**
     * The class name of the lean entity value object that is returned for a result.
     *
     * @var string
     */
    protected $entity;
    
    /**
     * Convert a database result to an entity
     *
     * @param mixed $result
     *
     * @return \Illuminate\Support\Collection|\Nwidart\Modules\Entities\Entity|bool|null
     */
    protected function convertToEntityResult($result)
    {
        // Handle non-entity result
        if (is_null($result) || is_bool($result) || is_integer($result)) {
            return $result;
        }
        
        if ($result instanceof Collection) {
            return $this->convertToEntityCollection($result);
        }
        
        return $this->convertToEntity($result);
    }
    
    /**
     * Convert all objects in a result collection to entities.
     *
     * @param \Illuminate\Support\Collection $collection
     *
     * @return \Illuminate\Support\Collection
     */
    protected function convertToEntityCollection(Collection $collection) : Collection
    {
        return $collection->map(function($item) {
            return $this->convertToEntityResult($item);
        });
    }
    
    /**
     * Convert a result object to an entity.
     *
     * @param mixed $object
     *
     * @return \Nwidart\Modules\Entities\Entity
     */
    protected function convertToEntity($object) : Entity
    {
        return EntityFactory::createFromObject($this->entity, $object);
    }
}