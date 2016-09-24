<?php

namespace Nwidart\Modules\Entities;

interface Entity
{
    /**
     * Fill the entity with a set of given attributes.
     *
     * @param array $attributes
     *
     * @return Entity
     */
    public function fill(array $attributes);
}