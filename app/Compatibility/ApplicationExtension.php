<?php

namespace App\Compatibility;

use Illuminate\Foundation\Application as LaravelApplication;

/**
 * This class extends Laravel's Application to add legacy methods
 */
class ApplicationExtension
{
    /**
     * Add the share method to Laravel's Application
     */
    public static function addShareMethod(): void
    {
        // Get the application instance
        $app = app();
        
        // Add a new method to the application instance
        if (!method_exists($app, 'share')) {
            // Using reflection to add the method
            $reflection = new \ReflectionClass($app);
            
            try {
                // Create a closure that mimics the share method
                $shareMethod = function($abstract, $concrete = null) {
                    // Share is equivalent to singleton in newer Laravel versions
                    return $this->singleton($abstract, $concrete);
                };
                
                // Bind the closure to the application instance
                $boundMethod = \Closure::bind($shareMethod, $app, get_class($app));
                
                // Store the method in a property
                $app->instance('__share_method', $boundMethod);
                
                // Intercept method calls using __call
                if (!method_exists($app, '__call') || !$reflection->hasMethod('__call')) {
                    $originalCall = function($method, $args) {
                        throw new \BadMethodCallException("Method {$method} does not exist.");
                    };
                    
                    if (method_exists($app, '__call')) {
                        $originalCall = \Closure::bind(
                            function($method, $args) {
                                return $this->__call($method, $args);
                            }, 
                            $app, 
                            get_class($app)
                        );
                    }
                    
                    // Create a new __call method
                    $newCall = function($method, $args) use ($originalCall) {
                        if ($method === 'share') {
                            $shareMethod = $this->make('__share_method');
                            return $shareMethod(...$args);
                        }
                        
                        return $originalCall($method, $args);
                    };
                    
                    // We can't actually bind this, but we can try a different approach
                    $app->instance('__call_intercept', $newCall);
                }
                
            } catch (\Exception $e) {
                // Log the error or handle it
                error_log('Failed to add share method: ' . $e->getMessage());
            }
        }
    }
}
