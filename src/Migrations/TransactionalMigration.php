<?php

namespace Nwidart\Modules\Migrations;

use Closure;
use Exception;
use Illuminate\Database\Migrations\Migration as IlluminateMigration;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;

abstract class TransactionalMigration extends IlluminateMigration
{
    /**
     * Database manager
     *
     * @var \Illuminate\Database\DatabaseManager
     */
    protected $manager;
    
    /**
     * Database connection
     *
     * @var \Illuminate\Database\Connection
     */
    protected $connection;
    
    /**
     * Database schema manager
     *
     * @var \Illuminate\Database\Schema\Builder
     */
    protected $builder;
    
    /**
     * List of all tables related to this migration
     *
     * You can add them here and use the dropAll() method in down().
     *
     * Why? Because it's easier and safer, because dropAll() will check
     * if the table exists before trying to delete it.
     *
     * @var array
     */
    protected $tables = [];
    
    /**
     * Check if this is a Laravel application
     */
    protected function isLaravel()
    {
        return function_exists('app') && app() instanceof \Illuminate\Foundation\Application;
    }
    
    /**
     * Check if a table exists.
     *
     * @param string $table
     *
     * @return mixed
     */
    protected function tableExists($table)
    {
        return $this->builder->hasTable($table);
    }
    
    /**
     * Check the database connection and use of the Laravel framework.
     *
     * @throws \Exception
     */
    protected function connect()
    {
        if ($this->isLaravel()) {
            $this->manager = app('db');
            $this->connection = $this->manager->connection();
            $this->builder = $this->connection->getSchemaBuilder();
        } else {
            throw new Exception('This migrator must be ran from inside a Laravel application.');
        }
    }
    
    /**
     * Execute the migrationm command inside a transaction layer.
     *
     * @param string $method
     */
    protected function executeInTransaction($method)
    {
        try {
            $this->connection->beginTransaction();
            
            $this->{$method}();
            
            $this->connection->commit();
        } catch (Exception $exception) {
            $this->connection->rollback();
            
            $this->handleException($exception);
        }
    }
    
    /**
     * Handle an exception.
     *
     * @param \Exception $exception
     */
    protected function handleException($exception)
    {
        $previous = $exception->getPrevious();
        
        if ($exception instanceof QueryException) {
            throw new $exception($exception->getMessage(), $exception->getBindings(), $previous);
        } else {
            throw new $exception($exception->getMessage(), $previous);
        }
    }
    
    /**
     * Create a table.
     *
     * @param string $table
     * @param Closure $callback
     *
     * @return \Illuminate\Support\Fluent
     */
    protected function create($table, $callback)
    {
        return $this->builder->create($table, $callback);
    }
    
    /**
     * Safely drop a column from a table.
     *
     * @param $tableName
     * @param $column
     */
    protected function dropColumn($tableName, $column)
    {
        // Check for its existence before dropping
        if ($this->builder->hasColumn($tableName, $column)) {
            $this->builder->table($tableName, function (Blueprint $table) use ($column) {
                $table->dropColumn($column);
            });
        }
    }
    
    /**
     * Safely drop a table.
     *
     * @param string $tables
     * @param bool $ignoreKeyConstraints
     */
    protected function drop($tables, $ignoreKeyConstraints = false)
    {
        if ($ignoreKeyConstraints) {
            $this->connection->statement('SET FOREIGN_KEY_CHECKS=0;');
        }
        
        if (! is_array($tables)) {
            $tables = [$tables];
        }
        
        foreach ($tables as $table) {
            if ($this->tableExists($table)) {
                $this->builder->drop($table);
            }
        }
        
        if ($ignoreKeyConstraints) {
            $this->connection->statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }
    
    /**
     * Safely drop all tables.
     *
     * @param bool $ignoreKeyConstraints
     */
    protected function dropAllTables($ignoreKeyConstraints = false)
    {
        $this->drop($this->tables);
    }
    
    /**
     * The abstracted up() method.
     *
     * Do not use up(), use this one instead.
     */
    abstract protected function migrateUp();
    
    /**
     * The abstracted down() method.
     *
     * Do not use down(), use this one instead.
     */
    abstract protected function migrateDown();
    
    /**
     * The Laravel Migrator up() method.
     */
    public function up()
    {
        $this->connect();
        $this->executeInTransaction('migrateUp');
    }
    
    /**
     * The Laravel Migrator down() method.
     */
    public function down()
    {
        $this->connect();
        $this->executeInTransaction('migrateDown');
    }
}