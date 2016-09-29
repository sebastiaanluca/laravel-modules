<?php

namespace Nwidart\Modules\Factories;

use Exception;
use Illuminate\Support\Collection;
use Nwidart\Modules\Entities\Entity;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionProperty;

class EntityFactory
{
    /**
     * Create an entity from an array of attributes.
     *
     * @param string $entity
     * @param array $attributes
     *
     * @return \Nwidart\Modules\Entities\Entity
     */
    public static function create(string $entity, array $attributes) : Entity
    {
        /** @var \Nwidart\Modules\Entities\Entity $entity */
        $entity = new $entity;
        
        return static::fill($entity, $attributes);
    }
    
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
     * @param bool $casts Cast source variables to the types defined in the Entity's variable doc blocks.
     *
     * @return \Nwidart\Modules\Entities\Entity
     * @throws Exception
     */
    protected static function fill(Entity $entity, array $attributes, $casts = true) : Entity
    {
        $getters = static::getDynamicGetters($entity);
        $setters = static::getDynamicSetters($entity);
        
        $types = static::getCastTypes($entity);
        
        foreach ($attributes as $attribute => $value) {
            $type = $types[$attribute];
            
            // Dynamic value setting
            if (in_array($attribute, $setters)) {
                call_user_func([$entity, 'set' . title_case($attribute) . 'Attribute'], $value);
                
                $value = $entity->{$attribute};
            }
            
            // Second conditional checks if there's something to cast to (i.e. missing doc block)
            if ($casts && ! is_null($type)) {
                $value = static::castAttribute($attribute, $value, $type, $nullable = in_array($attribute, $getters));
            }
            
            $entity->{$attribute} = $value;
        }
        
        return $entity;
    }
    
    /**
     * Get the variable cast types of the given attributes for an entity.
     *
     * @param \Nwidart\Modules\Entities\Entity $entity
     *
     * @return array
     * @throws Exception
     */
    protected static function getCastTypes(Entity $entity) : array
    {
        $properties = array_keys(get_object_vars($entity));
        
        $factory = DocBlockFactory::createInstance();
        
        $casts = array_fill_keys($properties, null);
        
        foreach (array_keys($casts) as $property) {
            $reflection = new ReflectionProperty($entity, $property);
            
            // No doc block found means no casting
            if (! $reflection->getDocComment()) {
                continue;
            }
            
            $doc = $factory->create($reflection);
            $types = $doc->getTagsByName('var');
            
            if (count($types) > 1) {
                throw new Exception('An entity\'s doc block cannot have more than one @var tag.');
            }
            
            // No @var tags means no casting
            if (! count($types)) {
                continue;
            }
            
            // Get the native type or namespaced class of the variable
            $type = (string)head($types)->getType();
            
            // Handle compound types by using the first found
            $type = explode('|', $type);
            
            $casts[$property] = $type;
        }
        
        return $casts;
    }
    
    /**
     * @param $attribute
     * @param $value
     * @param $type
     * @param $nullable
     *
     * @return mixed
     * @throws Exception
     */
    // TODO: write more structured
    protected static function castAttribute($attribute, $value, $type, bool $nullable = false)
    {
        // TODO: handle json/array
        
        if (is_array($type)) {
            // Either an attribute is explicitly marked as nullable
            // or it's a dynamic attribute that'll get filled now
            // or later through a dynamic attribute method.
            $nullable = $nullable ?: in_array('null', $type);
            $type = head($type);
        }
        
        if (is_null($value) && ! $nullable) {
            throw new Exception('Entity attribute value ' . $attribute . ' cannot be null.');
        }
        
        if (is_null($value)) {
            
        } elseif (class_exists($type)) {
            $value = new $type($value);
        } elseif (is_scalar($type)) {
            settype($value, $type);
        }
        
        return $value;
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
     * Get the names of the dynamic getters.
     *
     * @param \Nwidart\Modules\Entities\Entity $entity
     *
     * @return array
     */
    protected static function getDynamicGetters(Entity $entity) : array
    {
        return collect(get_class_methods($entity))
            ->between('get', 'Attribute')
            ->methodize('camel_case')->all();
    }
    
    /**
     * Get the names of the dynamic setters.
     *
     * @param \Nwidart\Modules\Entities\Entity $entity
     *
     * @return array
     */
    protected static function getDynamicSetters(Entity $entity) : array
    {
        return collect(get_class_methods($entity))
            ->between('set', 'Attribute')
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
        return collect(static::getDynamicGetters($entity))->reduce(function(Collection $items, $field) use ($entity) {
            $items[$field] = call_user_func([$entity, 'get' . title_case($field) . 'Attribute']);
            
            return $items;
        }, collect())->all();
    }
}