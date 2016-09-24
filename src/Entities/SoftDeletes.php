<?php

namespace Nwidart\Modules\Entities;

trait SoftDeletes
{
    /**
     * @var \Carbon\Carbon
     */
    public $deleted_at;
}