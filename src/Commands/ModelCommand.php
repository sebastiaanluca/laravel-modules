<?php

namespace Nwidart\Modules\Commands;

use Nwidart\Modules\Support\Stub;
use Nwidart\Modules\Traits\ModuleCommandTrait;

class ModelCommand extends GeneratorCommand
{
    use ModuleCommandTrait;
    use GeneratesFromTable;
    use UsesImportsAndTraits;
    
    /**
     * The name of argument name.
     *
     * @var string
     */
    protected $argumentName = 'name';
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:make:model
                            {name : The name of the model}
                            {module : The name of the module to create the model in}
                            {--table= : Base the model on the structure of an existing database table}
                            {--connection= : The database connection to use}';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new model in the given module.';
    
    /**
     * @return mixed
     */
    protected function getTemplateContents()
    {
        $module = $this->laravel['modules']->findOrFail($this->getFullyQualifiedName());
        
        if ($this->hasTable()) {
            /** @var \Nwidart\Modules\Support\TableReader $reader */
            $reader = $this->getTableReader();
            
            if ($this->hasConnection()) {
                $connection = $reader->getConnection()->getName();
                $connection = <<<PHP
    
    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected \$connection = '$connection';
    
PHP;
            }
            
            $table = $reader->getTable();
            $guarded = $this->format($reader->getGuarded());
            $casts = $this->format($reader->getCasts());
            $dates = $this->format($reader->getDates());
    
            if ($reader->usesSoftDelete()) {
                $this->imports[] = 'Illuminate\\Database\\Eloquent\\SoftDeletes';
                $this->traits[] = 'SoftDeletes';
            }
    
            if (! $reader->usesTimestamps()) {
                $timestamps= <<<PHP

    
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public \$timestamps = false;
PHP;

            }
        }
        
        return (new Stub('model.stub', [
            'NAMESPACE' => $this->getClassNamespace($module),
            'CLASS' => $this->getClass(),
            'CONNECTION' => $connection ?? '',
            'TABLE' => $table ?? '',
            'GUARDED' => $guarded ?? '',
            'TIMESTAMPS' => $timestamps ?? '',
            'CASTS' => $casts ?? '',
            'DATES' => $dates ?? '',
            
            'IMPORTS' => $this->getTemplateImports(),
            'TRAITS' => $this->getTraits(),
        ]))->render();
    }
    
    /**
     * Get the destination file path.
     *
     * @return string
     */
    protected function getDestinationFilePath()
    {
        $path = $this->laravel['modules']->getModulePath($this->getFullyQualifiedName());
        
        $subPath = $this->laravel['modules']->config('paths.generator.model');
        
        return "$path/$subPath/{$this->getClass()}.php";
    }
    
    /**
     * Get default namespace.
     *
     * @return string
     */
    protected function getDefaultNamespace()
    {
        return 'Entities';
    }
}
