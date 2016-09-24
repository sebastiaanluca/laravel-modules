<?php

namespace Nwidart\Modules\Entities;

trait Timestamps
{
    /**
     * @var \Carbon\Carbon
     */
    public $created_at;
    
    /**
     * @var \Carbon\Carbon
     */
    public $updated_at;
}