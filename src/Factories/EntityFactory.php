<?php

namespace Nwidart\Modules\Factories;

use Illuminate\Support\Collection;
use Nwidart\Modules\Entities\Entity;

class EntityFactory
{
    /**
     * Generate a new entity of a certain type based on the given object.
     *
     * @param string $entity
     * @param mixed $object
     *
     * @return \Nwidart\Modules\Entities\Entity
     */
    public static function createFromObject(string $entity, $object) : Entity
    {
        /** @var \Nwidart\Modules\Entities\Entity $entity */
        $entity = new $entity;
        
        static::fill($entity, static::getStaticAttributes($entity, $object));
        static::fill($entity, static::getDynamicAttributes($entity));
        
        return $entity;
    }
    
    /**
     * @param \Nwidart\Modules\Entities\Entity $entity
     * @param array $attributes
     *
     * @return \Nwidart\Modules\Entities\Entity
     */
    protected static function fill(Entity $entity, array $attributes) : Entity
    {
        foreach ($attributes as $attribute => $value) {
            $entity->{$attribute} = $value;
        }
        
        return $entity;
    }
    
    /**
     * Get the names of the static fields.
     *
     * @param \Nwidart\Modules\Entities\Entity $entity
     *
     * @return array
     */
    protected static function getStaticFields(Entity $entity) : array
    {
        $fields = array_keys(get_object_vars($entity));
        
        // The following removes the default static value of an attribute
        // and disables using it in its own dynamic attribute method.
        //        $dynamicFields = static::getDynamicFields($entity);
        //        $fields = array_diff($fields, $dynamicFields);
        
        return $fields;
    }
    
    /**
     * Get all the attributes and their values.
     *
     * @param \Nwidart\Modules\Entities\Entity $entity
     * @param mixed $object
     *
     * @return array
     */
    protected static function getStaticAttributes(Entity $entity, $object) : array
    {
        $fields = static::getStaticFields($entity);
        
        $attributes = [];
        
        foreach ($fields as $field) {
            $attributes[$field] = $object->{$field};
        }
        
        return $attributes;
    }
    
    /**
     * Get the names of the dynamic fields.
     *
     * @param \Nwidart\Modules\Entities\Entity $entity
     *
     * @return array
     */
    protected static function getDynamicFields(Entity $entity) : array
    {
        return collect(get_class_methods($entity))
            ->between('get', 'Attribute')
            ->methodize('camel_case')->all();
    }
    
    /**
     * Get the attributes and values built up from dynamically defined methods.
     *
     * @param \Nwidart\Modules\Entities\Entity $entity
     *
     * @return array
     */
    protected static function getDynamicAttributes(Entity $entity)
    {
        return collect(static::getDynamicFields($entity))->reduce(function(Collection $items, $field) use ($entity) {
            $items[$field] = call_user_func([$entity, 'get' . title_case($field) . 'Attribute']);
            
            return $items;
        }, collect())->all();
    }
}