<?php

namespace Nwidart\Modules\Commands;

use Illuminate\Console\Command;
use Nwidart\Modules\Exceptions\FileAlreadyExistException;
use Nwidart\Modules\Generators\FileGenerator;
use Nwidart\Modules\Support\Stub;
use Nwidart\Modules\Traits\ModuleCommandTrait;

abstract class MultiGeneratorCommand extends Command
{
    use ModuleCommandTrait;
    
    /**
     * The name of 'name' argument.
     *
     * @var string
     */
    protected $argumentName = '';
    
    /**
     * The files to generate.
     *
     * @var array
     */
    protected $files = [];
    
    /**
     * Get template contents.
     *
     * @param string $stub
     *
     * @return string
     */
    protected function getTemplateContentsFor($stub)
    {
        $module = $this->getModule();
    
        return (new Stub($stub, [
            'MODULE_NAME' => $module->getLowerName(),
        ]))->render();
    }
    
    /**
     * Get the destination file path.
     *
     * @param string $type
     * @param string $extension
     *
     * @return string
     */
    protected function getDestinationFilePathFor($type, $extension)
    {
        $module = $this->getModule();
        
        $path = $this->laravel['modules']->getModulePath($module->getFullyQualifiedName());
        $target = $this->laravel['modules']->config('paths.generator.' . $type);
        
        return "{$path}/{$target}/{$module->getLowerName()}.{$extension}";
    }
    
    
    /**
     * @param string $type
     * @param string $stub
     * @param string $extension
     */
    protected function generateFile($type, $stub, $extension)
    {
        $path = str_replace('\\', '/', $this->getDestinationFilePathFor($type, $extension));
        
        if (! $this->laravel['files']->isDirectory($dir = dirname($path))) {
            $this->laravel['files']->makeDirectory($dir, 0777, true);
        }
        
        $contents = $this->getTemplateContentsFor($stub);
        
        try {
            with(new FileGenerator($path, $contents))->generate();
            
            $this->info("Created : {$path}");
        } catch (FileAlreadyExistException $e) {
            $this->error("File : {$path} already exists.");
        }
    }
    
    /**
     * Get class name.
     *
     * @return string
     */
    public function getClass()
    {
        return class_basename($this->argument($this->argumentName));
    }
    
    /**
     * Get default namespace.
     *
     * @return string
     */
    public function getDefaultNamespace()
    {
        return '';
    }
    
    /**
     * Get class namespace.
     *
     * @param \Nwidart\Modules\Module $module
     *
     * @return string
     */
    public function getClassNamespace($module)
    {
        $extra = str_replace($this->getClass(), '', $this->argument($this->argumentName));
        $extra = str_replace('/', '\\', $extra);
        
        $namespace = $module->getNamespace();
        $namespace .= '\\' . $this->getDefaultNamespace();
        $namespace .= '\\' . $extra;
        
        return trim($namespace, '\\');
    }
    
    /**
     * Execute the console command.
     */
    public function fire()
    {
        foreach ($this->files as $type => list($stub, $extension)) {
            $this->generateFile($type, $stub, $extension);
        }
    }
}
