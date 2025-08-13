<?php

namespace App\Compatibility;

use Illuminate\Support\ServiceProvider;

class CompatibilityServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // No need to do anything special here
        // Our custom Application class already adds the share method
    }
}
