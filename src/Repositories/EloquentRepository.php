<?php

namespace Nwidart\Modules\Repositories;

use Illuminate\Support\Collection;
use Nwidart\Modules\Exceptions\Repositories\NoRecordsFound;
use Rinvex\Repository\Repositories\EloquentRepository as BaseEloquentRepository;

class EloquentRepository extends BaseEloquentRepository
{
    use PerformsActions, HandlesMethodExtensions;
    
    /**
     * Get the dynamically handled method extensions.
     *
     * @return array
     */
    protected function getMethodExtensions() : array
    {
        return [
            'OrFail' => [$this, 'validateResult'],
        ];
    }
    
    /**
     * Throw exceptions if a result is not valid.
     *
     * @param mixed $result
     * @param string $method
     *
     * @return mixed
     */
    protected function validateResult($result, string $method)
    {
        if (is_null($result)) {
            throw NoRecordsFound::emptyResult();
        }
        
        if ($result instanceof Collection && count($result) <= 0) {
            throw NoRecordsFound::emptyResultSet();
        }
        
        return $result;
    }
    
    /**
     * Create a new entity with the given attributes.
     *
     * @param array $attributes
     *
     * @return array
     */
    public function create(array $attributes = [])
    {
        return $this->performAction('create', $attributes);
    }
    
    /**
     * Update an entity with the given attributes.
     *
     * @param mixed $id
     * @param array $attributes
     *
     * @return array
     */
    public function update($id, array $attributes = [])
    {
        return $this->performAction('update', $id, $attributes);
    }
    
    /**
     * Delete an entity with the given id.
     *
     * @param mixed $id
     *
     * @return array
     */
    public function delete($id)
    {
        return $this->performAction('delete', $id);
    }
}