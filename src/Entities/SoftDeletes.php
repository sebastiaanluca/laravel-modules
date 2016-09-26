<?php

namespace Nwidart\Modules\Entities;

trait SoftDeletes
{
    /**
     * @var \Carbon\Carbon|null
     */
    public $deleted_at;
}