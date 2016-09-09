<?php

namespace Nwidart\Modules\Commands;

use Nwidart\Modules\Support\Stub;
use Nwidart\Modules\Support\TableReader;
use Nwidart\Modules\Traits\ModuleCommandTrait;

class ModelCommand extends GeneratorCommand
{
    use ModuleCommandTrait;
    
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
                            {--table= : Base the model on the structure of an existing database table}';
    
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
        
        if ($table = $this->option('table')) {
            $reader = app(TableReader::class)->read($table);
            
            $table = $reader->getTable();
            $guarded = $this->format($reader->getGuarded());
            $casts = $this->format($reader->getCasts());
            $dates = $this->format($reader->getDates());
        }
        
        return (new Stub('model.stub', [
            'NAMESPACE' => $this->getClassNamespace($module),
            'CLASS' => $this->getClass(),
            'TABLE' => $table ?? '',
            'GUARDED' => $guarded ?? '',
            'CASTS' => $casts ?? '',
            'DATES' => $dates ?? '',
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
        
        return "$path/$subPath/{$this->getModelName()}.php";
    }
    
    /**
     * @return mixed|string
     */
    protected function getModelName()
    {
        return studly_case($this->argument('name'));
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
