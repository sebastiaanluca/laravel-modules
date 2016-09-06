<?php

namespace Nwidart\Modules\Migrations;

use Exception;

abstract class TransactionalMigration extends Migration
{
    /**
     * Execute the migrationm command inside a transaction layer.
     *
     * @param string $method
     */
    protected function executeInTransaction($method)
    {
        try {
            $this->database->beginTransaction();
            
            $this->{$method}();
            
            $this->database->commit();
        } catch (Exception $exception) {
            $this->database->rollback();
            
            $this->handleException($exception);
        }
    }
    
    /**
     * The Laravel migrator up() method.
     */
    public function up()
    {
        $this->connect();
        $this->executeInTransaction('migrateUp');
    }
    
    /**
     * The Laravel migrator down() method.
     */
    public function down()
    {
        $this->connect();
        $this->executeInTransaction('migrateDown');
    }
}