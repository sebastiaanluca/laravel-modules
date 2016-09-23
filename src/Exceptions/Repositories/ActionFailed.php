<?php

namespace Nwidart\Modules\Exceptions\Repositories;

use RuntimeException;

class ActionFailed extends RuntimeException
{
    /**
     * Unable to perform a certain database action.
     *
     * @param string $method
     */
    public static function create(string $method)
    {
        throw new static(title_case($method) . ' action failed.');
    }
}