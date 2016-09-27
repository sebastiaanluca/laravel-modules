<?php

namespace Nwidart\Modules\Commands;

trait UsesImportsAndTraits
{
    /**
     * @var array
     */
    protected $imports = [];
    
    /**
     * @var array
     */
    protected $traits = [];
    
    /**
     * @return string
     */
    protected function getTemplateImports() : string
    {
        return collect($this->imports)->map(function($import) {
            return 'use ' . $import . ';' . PHP_EOL;
        })->implode('');
    }
    
    /**
     * @return string
     */
    protected function getTraits() : string
    {
        if (! count($this->traits)) {
            return '';
        }
        
        $traits = collect($this->traits)->implode(', ');
        
        return PHP_EOL . '    use ' . $traits . ';' . PHP_EOL . '    ';
    }
}