<?php

namespace Nwidart\Modules\Commands;

use Nwidart\Modules\Support\TableReader;

trait GeneratesFromTable
{
    /**
     * @var \Nwidart\Modules\Support\TableReader
     */
    protected $tableReader;
    
    /**
     * @return bool
     */
    protected function hasTableOption() : bool
    {
        return $this->hasOption('table');
    }
    
    /**
     * @return string
     */
    protected function getTable() : string
    {
        return $this->option('table');
    }
    
    /**
     * @return bool
     */
    protected function hasConnectionOption() : bool
    {
        return $this->hasOption('connection');
    }
    
    /**
     * @return string
     */
    protected function getConnection() : string
    {
        return $this->option('connection');
    }
    
    /**
     * Get a table reader instance.
     *
     * Optionally sets the database connection and automatically reads the table info too.
     *
     * @param bool $read
     *
     * @return \Nwidart\Modules\Support\TableReader
     */
    protected function getTableReader(bool $read = true) : TableReader
    {
        // Local caching
        if ($this->tableReader) {
            return $this->tableReader;
        }
        
        $this->tableReader = app(TableReader::class);
        
        if ($connection = $this->option('connection')) {
            $this->tableReader->setConnection($connection);
        }
        
        if ($read) {
            $this->tableReader->read($this->getTable());
        }
        
        return $this->tableReader;
    }
}