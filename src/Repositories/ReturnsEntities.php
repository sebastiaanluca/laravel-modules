<?php

namespace Nwidart\Modules\Repositories;

use Illuminate\Support\Collection;
use Nwidart\Modules\Entities\Entity;

trait ReturnsEntities
{
    /**
     * The lean entity value object that is returned for a result.
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
        if (is_null($result) || is_bool($result)) {
            return $result;
        }
        
        if ($result instanceof Collection) {
            return $this->convertToEntityCollection($result);
        }
        
        return $this->convertToEntity($result);
    }
    
    /**
     * @param mixed $item
     *
     * @return \Nwidart\Modules\Entities\Entity
     */
    protected function convertToEntity($item) : Entity
    {
        /** @var \Nwidart\Modules\Entities\Entity $entity */
        $entity = new $this->entity;
        
        $fields = array_keys(get_object_vars($entity));
        
        foreach ($fields as $field) {
            $entity->{$field} = $item->{$field};
        }
        
        $this->parseDynamicAttributes($entity);
        
        return $entity;
    }
    
    /**
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
     * Set attributes from dynamic methods.
     *
     * @param \Nwidart\Modules\Entities\Entity $entity
     */
    protected function parseDynamicAttributes(Entity $entity)
    {
        $methods = get_class_methods($entity);
        
        collect($methods)->between('get', 'Attribute')->each(function($attribute)use($entity) {
            $entity->{camel_case($attribute)} = call_user_func([$entity, 'get' . $attribute . 'Attribute']);
        });
    }
}