<?php

namespace Nwidart\Modules\Validators;

use Illuminate\Foundation\Http\FormRequest;

abstract class Validator extends FormRequest
{
    /**
     * Automatically authorize any request by default.
     *
     * @return bool
     */
    public function authorize() : bool
    {
        return true;
    }
    
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    abstract public function rules() : array;
    
    /**
     * Get validated user input.
     *
     * Check if input corresponds with fields under validation.
     *
     * @param bool $clean Remove the input fields whose values are empty.
     *
     * @return array
     */
    public function getValidInput(bool $clean = false) : array
    {
        // Make sure any multi-dimensional input array matches our dotted rule keys
        $input = array_dot($this->input());
        
        // Match the input attributes against the existing rules
        $input = array_only($input, array_keys($this->rules()));
        
        // Reverse dot flattening to match original input
        $input = array_expand($input);
        
        // Clean empty values
        if ($clean) {
            $input = $this->sanitizeInput($input);
        }
        
        return $input;
    }
    
    /**
     * Get validated user input.
     *
     * Check if input corresponds with fields under validation.
     *
     * @param bool $clean Remove the input fields whose values are empty.
     *
     * @return array
     */
    public function valid(bool $clean = false) : array
    {
        return $this->getValidInput($clean);
    }
    
    /**
     * Remove null and empty values from the input.
     *
     * @param array $input
     *
     * @return array
     */
    public function sanitizeInput(array $input) : array
    {
        return $input = collect($input)->reject(function($value) {
            return is_null($value) || empty($value);
        })->toArray();
    }
}