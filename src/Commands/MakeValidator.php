<?php

namespace Nwidart\Modules\Commands;

use Nwidart\Modules\Support\Stub;
use Nwidart\Modules\Support\TableReader;
use Nwidart\Modules\Traits\ModuleCommandTrait;

class MakeValidator extends GeneratorCommand
{
    use ModuleCommandTrait;
    use GeneratesFromTable;
    
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
                            {--table= : Pre-fill the fields to validate by reading them from a database table}
                            {--connection= : The database connection to use}';
    
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
        if (! $this->hasTableOption()) {
            return '';
        }
        
        $reader = $this->getTableReader();
        $validatable = $this->getValidatableFields($reader);
        
        $rules = $reader->getRawFields()->filter(function($item) use ($validatable) {
            // Only keep validatable and non-ID fields
            return in_array($item['field'], $validatable) && ! ends_with($item['field'], '_id');
        })->map(function($item) use ($reader) {
            return [
                'field' => $item['field'],
                'rules' => $this->getRulesForField($item, $reader),
            ];
        })->pluck('rules', 'field')->toArray();
        
        return $this->format($rules, 2);
    }
    
    /**
     * Get the fields under validation.
     *
     * @param TableReader $table
     *
     * @return array
     */
    protected function getValidatableFields(TableReader $table) : array
    {
        $validatable = $table->getFillable();
        
        if ($table->hasField('password')) {
            $validatable[] = 'password';
        }
        
        return $validatable;
    }
    
    /**
     * Get the validation rules for a given table field.
     *
     * @param array $item
     * @param \Nwidart\Modules\Support\TableReader $table
     *
     * @return string
     */
    protected function getRulesForField(array $item, TableReader $table) : string
    {
        $rules = [];
        
        $this->addRequiredRule($item, $rules);
        $this->addVarcharFieldRules($item, $rules);
        $this->addBooleanFieldRules($item, $rules);
        $this->addEmailFieldRules($item, $rules);
        $this->addTextFieldRules($item, $rules);
        $this->addDecimalFieldRules($item, $rules);
        $this->addJsonFieldRules($item, $rules);
        $this->addTimestampFieldRules($item, $rules);
        $this->addUniqueFieldRules($item, $rules, $table);
        
        // TODO: support for signed/unsigned integers, floats, …
        
        return join('|', $rules);
    }
    
    /**
     * Add the required rule if applicable.
     *
     * @param array $item
     * @param array $rules
     */
    protected function addRequiredRule(array $item, array &$rules)
    {
        if ($item['null'] === 'NO' && is_null($item['default'])) {
            $rules[] = 'required';
        }
    }
    
    /**
     * Add rules for a varchar field if applicable.
     *
     * @param array $item
     * @param array $rules
     */
    protected function addVarcharFieldRules(array $item, array &$rules)
    {
        if (str_contains($item['type'], 'varchar')) {
            $length = preg_replace('/\D/', '', $item['type']);
            
            $rules[] = 'string|max:' . $length;
        }
    }
    
    /**
     * Add rules for a boolean field if applicable.
     *
     * @param array $item
     * @param array $rules
     */
    protected function addBooleanFieldRules(array $item, array &$rules)
    {
        if ($item['type'] === 'tinyint(1)') {
            $rules[] = 'boolean';
        }
    }
    
    /**
     * Add rules for an e-mail field if applicable.
     *
     * @param array $item
     * @param array $rules
     */
    protected function addEmailFieldRules(array $item, array &$rules)
    {
        if ($item['field'] == 'email') {
            $rules = array_prepend($rules, 'bail');
            
            $rules[] = 'email';
        }
    }
    
    /**
     * Add rules for a text field if applicable.
     *
     * @param array $item
     * @param array $rules
     */
    protected function addTextFieldRules(array $item, array &$rules)
    {
        if ($item['type'] === 'text') {
            // 65535 bytes max for a text field divided by 4 bytes per char (utf8mb4)
            $rules[] = 'string|max:16383';
        }
    }
    
    /**
     * Add rules for a decimal (float) field if applicable.
     *
     * @param array $item
     * @param array $rules
     */
    protected function addDecimalFieldRules(array $item, array &$rules)
    {
        if (str_contains($item['type'], 'double')) {
            preg_match('~\((.*?)\)~', $item['type'], $match);
            
            list($base, $decimal) = explode(',', $match[1]);
            
            $base = str_repeat('9', $base);
            $decimal = str_repeat('9', $decimal);
            
            $rules[] = "numeric|between:0,$base.$decimal";
        }
    }
    
    /**
     * Add rules for a JSON field if applicable.
     *
     * @param array $item
     * @param array $rules
     */
    protected function addJsonFieldRules(array $item, array &$rules)
    {
        if ($item['type'] === 'json') {
            $rules[] = 'array';
        }
    }
    
    /**
     * Add rules for a timestamp field if applicable.
     *
     * @param array $item
     * @param array $rules
     */
    protected function addTimestampFieldRules(array $item, array &$rules)
    {
        if ($item['type'] === 'timestamp') {
            $rules[] = 'date_format:Y-m-d H:i:s';
        }
    }
    
    /**
     * Add rules for a unique or composite unique field if applicable.
     *
     * @param array $item
     * @param array $rules
     * @param \Nwidart\Modules\Support\TableReader $table
     */
    protected function addUniqueFieldRules(array $item, array &$rules, TableReader $table)
    {
        if (in_array($item['key'], ['UNI', 'MUL'])) {
            $uniqueRule = 'unique:' . $table->getTable() . ',' . $item['field'];
            
            // Ignore (soft) deleted entries
            if ($table->usesSoftDelete()) {
                $uniqueRule .= ',NULL,id,deleted_at,NULL';
            }
            
            $rules[] = $uniqueRule;
        }
    }
}