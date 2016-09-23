<?php

namespace Nwidart\Modules\Repositories;

trait HandlesMethodExtensions
{
    /**
     * Get the dynamically handled method extensions.
     *
     * @return array
     */
    protected function getMethodExtensions() : array
    {
        return [];
    }
    
    /**
     * Check if a called method is has a dynamic part.
     *
     * @param string $method
     * @param string $extension
     *
     * @return bool
     */
    protected function currentCallIsMethodExtension(string $method, string $extension) : bool
    {
        return ends_with($method, $extension);
    }
    
    /**
     * Perform extra actions on the result of a given pseudo-method.
     *
     * @param string $method
     * @param string $extension
     * @param array $parameters
     * @param callable $callback
     *
     * @return mixed
     */
    protected function callDynamicMethod(string $method, string $extension, array $parameters, callable $callback)
    {
        $method = str_replace($extension, '', $method);
        
        // Prevent an infinite loop by first checking the method-to-call
        if (public_method_exists($this, $method)) {
            $result = call_user_func_array([$this, $method], $parameters);
        } else {
            // TODO: check if base class even has a parent (throw BadMethodException or leave as-is if false as we then can't proceed by calling the parent method)
            $result = parent::__call($method, $parameters);
        }
        
        return $callback($result, $method);
    }
    
    /**
     * Dynamically handle missing methods.
     *
     * @param string $method
     * @param array $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        foreach ($this->getMethodExtensions() as $extension => $callback) {
            if ($this->currentCallIsMethodExtension($method, $extension)) {
                return $this->callDynamicMethod($method, $extension, $parameters, $callback);
            }
        }
        
        // TODO: check if base class has a parent (throw BadMethodException if false as we then can't proceed by calling the parent method)
        
        // Default fallback
        return parent::__call($method, $parameters);
    }
}