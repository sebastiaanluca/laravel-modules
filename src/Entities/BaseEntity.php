<?php

namespace Nwidart\Modules\Entities;

abstract class BaseEntity implements Entity
{
    /**
     * Fill the entity with a set of given attributes.
     *
     * @param array $attributes
     *
     * @return Entity
     */
    public function fill(array $attributes)
    {
        foreach ($attributes as $attribute => $value) {
            $this->{$attribute} = $value;
        }
        
        return $this;
    }
    
    /**
     * Set attributes from dynamic methods.
     */
    public function parseDynamicAttributes()
    {
        $methods = get_class_methods($this);
        
        collect($methods)->between('get', 'Attribute')->each(function($attribute) {
            $this->{camel_case($attribute)} = call_user_func([$this, 'get' . $attribute . 'Attribute']);
        });
    }
}