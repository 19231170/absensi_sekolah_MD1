<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // No need to add share() method anymore
        // This is handled by our custom Application class
    }
    
    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        // Set timezone untuk PHP
        date_default_timezone_set('Asia/Jakarta');
    }
}
