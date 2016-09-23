<?php

namespace Nwidart\Modules\Exceptions\Repositories;

use RuntimeException;

class NoRecordsFound extends RuntimeException
{
    /**
     * The query returned an empty result.
     */
    public static function emptyResult()
    {
        throw new static('No record was found for that query.');
    }
    
    /**
     * The query returned an empty collection.
     */
    public static function emptyResultSet()
    {
        throw new static('The query returned an empty set of records.');
    }
}