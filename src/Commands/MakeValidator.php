<?php

namespace Nwidart\Modules\Commands;

use Nwidart\Modules\Support\Stub;
use Nwidart\Modules\Support\TableReader;
use Nwidart\Modules\Traits\ModuleCommandTrait;

class MakeValidator extends GeneratorCommand
{
    use ModuleCommandTrait;
    
    /**
     * The name of argument being used.
     *
     * @var string
     */
    protected $argumentName = 'resource';
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:make:validator
                            {resource : The singular name of the resource}
                            {module : The name of the module to create the validator in}
                            {--name= : The studly case class name of the validator}
                            {--table= : Pre-fill the fields to validate}';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a resource form request validator.';
    
    /**
     * Get the destination file path.
     *
     * @return string
     */
    protected function getDestinationFilePath()
    {
        $modulePath = $this->laravel['modules']->getModulePath($this->getFullyQualifiedName());
        
        $typePath = $this->laravel['modules']->config('paths.generator.validator');
        
        return "$modulePath/$typePath/{$this->getClassName()}.php";
    }
    
    /**
     * @return string
     */
    protected function getTemplateContents()
    {
        $module = $this->laravel['modules']->findOrFail($this->getFullyQualifiedName());
        
        return (new Stub($this->getStubName(), [
            'CLASS_NAMESPACE' => $this->getClassNamespace($module),
            'CLASS' => $this->getClassName(),
            'RULES' => $this->getRules(),
        ]))->render();
    }
    
    /**
     * Get the stub file name.
     *
     * @return string
     */
    protected function getStubName()
    {
        return 'validator.stub';
    }
    
    /**
     * Get the default class namespace.
     *
     * @return string
     */
    protected function getDefaultNamespace()
    {
        return 'Http\Validators';
    }
    
    /**
     * Get the resource controller name.
     *
     * @return string
     */
    protected function getClassName()
    {
        if ($name = $this->option('name')) {
            return $name;
        }
        
        return studly_case($this->argument($this->argumentName)) . 'Validator';
    }
    
    /**
     * Get the validation rules based on the resource table's structure.
     *
     * @return string
     */
    protected function getRules()
    {
        //'other_resource_id' => 'required|integer',
        //'name' => 'required|max:100',
        //'email' => 'bail|required|email|max:180|unique:tests,email,NULL,id,deleted_at,NULL',
        //'is_something' => 'boolean',
        //'decimal' => 'required|numeric|between:0,99.99',
        //'description' => 'required|max:9999',
        //'items' => 'array',
        //'solved_at' => 'date_format:Y-m-d H:i:s',
        
        if (! $table = $this->option('table')) {
            return '';
        }
        
        $table = app(TableReader::class)->read($table);
        $fillable = $table->getFillable();
        
        if ($table->hasField('password')) {
            $fillable[] = 'password';
        }
        
        // TODO: refactor into one big method and further down to several methods (use a mapping array and a loop/collection)
        
        $rules = $table->getRawFields()->filter(function($item) use ($fillable) {
            // Only keep fillable and non-ID fields
            return in_array($item['field'], $fillable) && ! ends_with($item['field'], '_id');
        })->map(function($item) use ($table) {
            /**
             * @var string $field
             * @var string $type
             * @var string $null
             * @var string $key
             * @var string $default
             * @var string $extra
             */
            extract($item);
            
            $rules = [];
            
            // Field is required
            if ($null === 'NO' && is_null($default)) {
                $rules[] = 'required';
            }
            
            if (str_contains($type, 'varchar')) {
                $length = preg_replace('/\D/', '', $type);
                
                $rules[] = 'string|max:' . $length;
            }
            
            if ($type === 'tinyint(1)') {
                $rules[] = 'boolean';
            }
            
            if ($field == 'email') {
                $rules = array_prepend($rules, 'bail');
                
                $rules[] = 'email';
            }
            
            if ($type === 'text') {
                // 65535 bytes max for a text field divided by 4 bytes per char (utf8mb4)
                $rules[] = 'string|max:16383';
            }
            
            if (str_contains($type, 'double')) {
                preg_match('~\((.*?)\)~', $type, $match);
                
                list($base, $decimal) = explode(',', $match[1]);
                
                $base = str_repeat('9', $base);
                $decimal = str_repeat('9', $decimal);
                
                $rules[] = "numeric|between:0,$base.$decimal";
            }
            
            if ($type === 'json') {
                $rules[] = 'array';
            }
            
            if ($type === 'timestamp') {
                $rules[] = 'date_format:Y-m-d H:i:s';
            }
            
            // Field has a unique or composite (unique) key
            if (in_array($key, ['UNI', 'MUL'])) {
                $uniqueRule = 'unique:' . $table->getTable() . ',' . $field;
                
                // Ignore (soft) deleted entries
                if ($table->usesSoftDelete()) {
                    $uniqueRule .= ',NULL,id,deleted_at,NULL';
                }
                
                $rules[] = $uniqueRule;
            }
            
            // TODO: support for signed/unsigned integers, floats, …
            
            return [
                'field' => $field,
                'rules' => join('|', $rules),
            ];
        })->pluck('rules', 'field')->toArray();
        
        return $this->format($rules, 2);
    }
}