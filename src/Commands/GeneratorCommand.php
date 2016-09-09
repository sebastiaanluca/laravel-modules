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
     * Format a given value as a string.
     *
     * @param mixed $value
     * @param int $indentationLevel
     *
     * @return string
     */
    protected function format($value, int $indentationLevel = 1) : string
    {
        if (is_array($value)) {
            return $this->formatAsArrayContents($value, $indentationLevel + 1);
        }
        
        return '';
    }
    
    /**
     * Format a given array as a string.
     *
     * @param array $value
     * @param int $indentationLevel
     *
     * @return string
     */
    protected function formatAsArrayContents(array $value, int $indentationLevel = 2) : string
    {
        $isMultiValue = count($value) > 0;
        $isAssociative = is_assoc_array($value);
        $indent = '    ';
        
        if (! $isMultiValue) {
            return str_wrap($value[0], "'");
        }
        
        $string = collect($value)->map(function($item, $key) use ($isAssociative) {
            if ($isAssociative) {
                return $key . "' => '" . $item;
            }
            
            return $item;
        })->map(function($item) use ($indent, $indentationLevel) {
            return PHP_EOL . str_repeat($indent, $indentationLevel) . str_wrap($item, "'") . ',';
        })->implode('');
        
        $string = $string . PHP_EOL . str_repeat($indent, $indentationLevel - 1);
        
        return $string;
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
