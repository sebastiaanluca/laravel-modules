<?php

namespace Nwidart\Modules\Commands;

use Nwidart\Modules\Support\Stub;
use Nwidart\Modules\Traits\ModuleCommandTrait;

class MakeEntity extends GeneratorCommand
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
    protected $signature = 'module:make:entity
                            {name : The title cased name of the entity}
                            {module : The name of the module to create the model in}
                            {--table= : Base the entity on the structure of an existing database table}
                            {--connection= : The database connection to use}';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new entity in the given module.';
    
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
    protected function getDefaultNamespace() : string
    {
        return 'Entities';
    }
    
    /**
     * @return mixed
     */
    protected function getTemplateContents() : string
    {
        $module = $this->laravel['modules']->findOrFail($this->getFullyQualifiedName());
        
        return (new Stub('entity.stub', [
            'NAMESPACE' => $this->getClassNamespace($module),
            'CLASS' => $this->getClass(),
            'PROPERTIES' => $this->getProperties(),
            
            'IMPORTS' => $this->getTemplateImports(),
            'TRAITS' => $this->getTraits(),
        ]))->render();
    }
    
    /**
     * @return string
     */
    // TODO: simplify/extract
    protected function getProperties() : string
    {
        if (! $this->hasTableOption()) {
            return '';
        }
        
        $table = $this->getTableReader();
        $fields = $table->getFields();
        
        $casts = $table->getCasts();
        $dates = $table->getDates();
        $nullable = array_without($table->getNullableFields(), ['created_at', 'updated_at', 'deleted_at']);
        
        // Convert cast types to native types
        $casts = collect($casts)->map(function($type) {
            switch ($type) {
                case 'integer':
                    return 'int';
                
                case 'boolean':
                    return 'bool';
                
                case 'json':
                    return 'array';
                
                default:
                    return $type;
            }
        })->all();
        
        if ($table->usesSoftDelete()) {
            $this->imports[] = 'Nwidart\\Modules\\Entities\\SoftDeletes';
            $this->traits[] = 'SoftDeletes';
            
            $fields = array_without($fields, ['deleted_at']);
        }
        
        if ($table->usesTimestamps()) {
            $this->imports[] = 'Nwidart\\Modules\\Entities\\Timestamps';
            $this->traits[] = 'Timestamps';
            
            $fields = array_without($fields, ['created_at', 'updated_at']);
        }
        
        $properties = '';
        
        foreach ($fields as $field) {
            $type = 'string';
            
            if (array_key_exists($field, $casts)) {
                $type = $casts[$field];
            } elseif (in_array($field, $dates)) {
                $type = '\\Carbon\\Carbon';
            }
            
            if (in_array($field, $nullable)) {
                $type .= '|null';
            }
            
            $properties .= <<<PHP
    /**
     * @var $type
     */
    public $$field;
PHP;
            if ($field != last($fields)) {
                $properties .= PHP_EOL . '    ' . PHP_EOL;
            }
        }
        
        return $properties;
    }
}
