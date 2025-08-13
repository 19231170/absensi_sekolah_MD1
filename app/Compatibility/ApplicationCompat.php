<?php

namespace App\Compatibility;

use Illuminate\Foundation\Application;

/**
 * Add compatibility methods to the Laravel Application class
 */
class ApplicationCompat
{
    /**
     * Register the compatibility patches
     */
    public static function register(): void
    {
        // Add a global function to intercept calls to share() method
        if (!method_exists(Application::class, 'share')) {
            // Get the Application instance
            $app = app();
            
            // Add the share method directly to the instance
            $app->singleton('__compatibility.share', function($app) {
                return function($abstract, $concrete = null) use ($app) {
                    return $app->singleton($abstract, $concrete);
                };
            });
            
            // Define a method to call the share functionality
            if (!function_exists('callShare')) {
                function callShare($app, $abstract, $concrete = null)
                {
                    if (method_exists($app, 'share')) {
                        return $app->share($abstract, $concrete);
                    } else {
                        return $app->singleton($abstract, $concrete);
                    }
                }
            }
        }
    }
}
