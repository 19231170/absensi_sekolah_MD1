<?php

namespace App\Providers;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class CompatibilityServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Add compatibility for Laravel 4's share() method to support older packages
        if (!method_exists(Application::class, 'share')) {
            require_once app_path('Compatibility/ApplicationLegacyCompat.php');
            
            // Apply the trait to the Application class using class_alias
            class_alias(\Illuminate\Foundation\Application::class, 'OriginalApplication');
            
            eval('
                namespace Illuminate\Foundation;
                
                class Application extends \OriginalApplication
                {
                    use \Illuminate\Foundation\ApplicationLegacyCompat;
                }
            ');
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
