<?php

namespace Nwidart\Modules\Exceptions\Repositories;

use Exception;
use RuntimeException;

class NoRecordsFound extends RuntimeException
{
    /**
     * @var string
     */
    public $entity;
    
    /**
     * NoRecordsFound constructor.
     *
     * @param string $message
     * @param string $entity
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct($message = '', $entity = '', $code = 0, Exception $previous = null)
    {
        $this->entity = $entity;
        
        parent::__construct($message, $code, $previous);
    }
    
    /**
     * String representation of the exception
     *
     * @return string the string representation of the exception.
     */
    public function __toString()
    {
        $class = get_class($this);
        $message = substr($this->message, 0, -1);
        
        return <<<STRING
$class
$message for entity $this->entity
In {$this->getFile()} line {$this->getLine()}
{$this->getTraceAsString()}
STRING;
    }
    
    /**
     * The query returned an empty result.
     *
     * @param string $entity
     */
    public static function emptyResult(string $entity = '')
    {
        throw new static('No record found.', $entity);
    }
    
    /**
     * The query returned an empty collection.
     *
     * @param string $entity
     */
    public static function emptyResultSet(string $entity = '')
    {
        throw new static('The query returned an empty set of records.', $entity);
    }
}