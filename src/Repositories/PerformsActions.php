<?php

namespace Nwidart\Modules\Repositories;

use Nwidart\Modules\Exceptions\Repositories\ActionFailed;
use Nwidart\Modules\Exceptions\Repositories\NoRecordsFound;

trait PerformsActions
{
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
        
        $this->validateAction($action, $result);
        
        return $this->extractActionResult($result, $action);
    }
}