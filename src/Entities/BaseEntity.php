<?php

namespace Nwidart\Modules\Entities;

abstract class BaseEntity implements Entity
{
    /**
     * The primary key.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    
    /**
     * Get the field that's used as primary key when retrieving and storing entities of this type.
     *
     * @return string
     */
    public function getKey() : string
    {
        return $this->primaryKey;
    }
    
    /**
     * Get the value of the primary key field.
     *
     * @return integer|string
     */
    public function getKeyValue()
    {
        return $this->{$this->getKey()};
    }
}