<?php

namespace Nwidart\Modules\Repositories;

use Nwidart\Modules\Exceptions\Repositories\ActionFailed;
use Nwidart\Modules\Exceptions\Repositories\NoRecordsFound;

trait PerformsActions
{
    /**
     * Perform a database action.
     *
     * @param string $action
     * @param array $parameters
     *
     * @return mixed
     */
    protected function performAction(string $action, ...$parameters)
    {
        $result = parent::$action(...$parameters);
        
        if ($action == 'create') {
            $result = $this->getFreshRecord($result);
        }
        
        $this->validateAction($action, $result);
        
        $result = $this->extractActionResult($result, $action);
        
        return $this->convertToEntityResult($result);
    }
    
    /**
     * Get a complete and updated record from the database.
     *
     * Instead of relying on the local version of a model when creating a record, fetch the newly
     * created version from the database which has all fields completely populated.
     *
     * @param array $result
     *
     * @return array
     */
    protected function getFreshRecord(array $result) : array
    {
        $local = $result[1];
        
        $fresh = $this->find($local->id);
        
        $result[1] = $fresh;
        
        return $result;
    }
    
    /**
     * Validate a database action such as create, update, or delete.
     *
     * @param string $action
     * @param mixed $result
     */
    protected function validateAction(string $action, $result)
    {
        if (! is_array($result)) {
            return;
        }
        
        // Action result instance is invalid
        if (count($result) > 1 && is_null($result[1])) {
            throw NoRecordsFound::emptyResult();
        }
        
        // Action itself failed to make any changes
        if ($result[0] === false) {
            throw ActionFailed::create($action);
        }
    }
    
    /**
     * Get the actual record from the action result.
     *
     * @param mixed $result
     * @param string|null $action
     *
     * @return mixed
     */
    protected function extractActionResult($result, $action = null)
    {
        if ($action == 'delete') {
            return $result[0];
        }
        
        return $result[1];
    }
}