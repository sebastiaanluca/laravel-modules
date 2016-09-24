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
}