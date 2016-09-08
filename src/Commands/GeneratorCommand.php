<?php

namespace Nwidart\Modules\Commands;

use Illuminate\Console\Command;
use Nwidart\Modules\Exceptions\FileAlreadyExistException;
use Nwidart\Modules\Generators\FileGenerator;

abstract class GeneratorCommand extends Command
{
    /**
     * The name of 'name' argument.
     *
     * @var string
     */
    protected $argumentName = '';
    
    /**
     * Get template contents.
     *
     * @return string
     */
    protected function getTemplateContents()
    {
        return '';
    }
    
    /**
     * Get the destination file path.
     *
     * @return string
     */
    abstract protected function getDestinationFilePath();
    
    /**
     * Get class name.
     *
     * @return string
     */
    protected function getClass()
    {
        return class_basename(studly_case($this->argument($this->argumentName)));
    }
    
    /**
     * Get default namespace.
     *
     * @return string
     */
    protected function getDefaultNamespace()
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
    protected function getClassNamespace($module)
    {
        $namespace = $module->getNamespace();
        $namespace .= '\\' . $this->getDefaultNamespace();
        
        return trim($namespace, '\\');
    }
    
    /**
     * Execute the console command.
     */
    public function fire()
    {
        $path = str_replace('\\', '/', $this->getDestinationFilePath());
        
        if (! $this->laravel['files']->isDirectory($dir = dirname($path))) {
            $this->laravel['files']->makeDirectory($dir, 0777, true);
        }
        
        $contents = $this->getTemplateContents();
        
        try {
            with(new FileGenerator($path, $contents))->generate();
            
            $this->info("Created: {$path}");
        } catch (FileAlreadyExistException $e) {
            $this->error("File: {$path} already exists.");
        }
    }
}
