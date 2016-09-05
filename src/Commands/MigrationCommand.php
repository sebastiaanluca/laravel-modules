<?php

namespace Nwidart\Modules\Commands;

use Illuminate\Support\Str;
use InvalidArgumentException;
use Nwidart\Modules\Support\Migrations\NameParser;
use Nwidart\Modules\Support\Migrations\SchemaParser;
use Nwidart\Modules\Support\Stub;
use Nwidart\Modules\Traits\ModuleCommandTrait;

class MigrationCommand extends GeneratorCommand
{
    use ModuleCommandTrait;
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:make:migration 
                            {name : The name of the migration (snake case)}
                            {module : The name of the module it should be created in}
                            {--fields= : The fields to create}
                            {--plain : Create a plain migration}';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new migration for the given module.';
    
    /**
     * @return string
     */
    protected function getFileName()
    {
        return date('Y_m_d_His_') . $this->getSchemaName();
    }
    
    /**
     * @return array|string
     */
    protected function getSchemaName()
    {
        return $this->argument('name');
    }
    
    /**
     * @return string
     */
    protected function getClassName()
    {
        return Str::studly($this->argument('name'));
    }
    
    /**
     * Get class name.
     *
     * @return string
     */
    protected function getClass()
    {
        return $this->getClassName();
    }
    
    /**
     * @throws \InvalidArgumentException
     *
     * @return mixed
     */
    protected function getTemplateContents()
    {
        $parser = new NameParser($this->argument('name'));
        
        if ($parser->isCreate()) {
            return Stub::create('migration/create.stub', [
                'class' => $this->getClass(),
                'table' => $parser->getTableName(),
                'fields' => $this->getSchemaParser()->render(),
            ]);
        } elseif ($parser->isAdd()) {
            return Stub::create('migration/add.stub', [
                'class' => $this->getClass(),
                'table' => $parser->getTableName(),
                'fields_up' => $this->getSchemaParser()->up(),
                'fields_down' => $this->getSchemaParser()->down(),
            ]);
        } elseif ($parser->isDelete()) {
            return Stub::create('migration/delete.stub', [
                'class' => $this->getClass(),
                'table' => $parser->getTableName(),
                'fields_down' => $this->getSchemaParser()->up(),
                'fields_up' => $this->getSchemaParser()->down(),
            ]);
        } elseif ($parser->isDrop()) {
            return Stub::create('migration/drop.stub', [
                'class' => $this->getClass(),
                'table' => $parser->getTableName(),
                'fields' => $this->getSchemaParser()->render(),
            ]);
        }
        
        throw new InvalidArgumentException('Invalid migration name');
    }
    
    /**
     * Get schema parser.
     *
     * @return SchemaParser
     */
    protected function getSchemaParser()
    {
        return new SchemaParser($this->option('fields'));
    }
    
    /**
     * @return mixed
     */
    protected function getDestinationFilePath()
    {
        $path = $this->laravel['modules']->getModulePath($this->getFullyQualifiedName());
        
        $generatorPath = $this->laravel['modules']->config('paths.generator.migration');
        
        return "{$path}/{$generatorPath}/{$this->getFileName()}.php";
    }
    
    /**
     * Run the command.
     */
    public function fire()
    {
        parent::fire();
        
        if (app()->environment() === 'testing') {
            return;
        }
        
        // TODO: remove?
        // $this->call('optimize');
    }
}
