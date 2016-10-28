<?php

namespace Nwidart\Modules\Exceptions;

use Exception;

class JsonException extends Exception
{
    /**
     * @param string|null $file
     *
     * @return \Nwidart\Modules\Exceptions\JsonException
     */
    public static function malformedJson($file = null)
    {
        if (is_null($file)) {
            $file = 'The JSON string or file';
        }
        
        return new JsonException($file . ' could not be read because it contains invalid syntax.');
    }
}
