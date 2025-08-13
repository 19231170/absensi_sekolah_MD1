<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Exports\FastExcelAbsensiExport;
use App\Imports\FastExcelSiswaImport;
use Rap2hpoutre\FastExcel\FastExcel;

class FastExcelServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register FastExcelAbsensiExport as a singleton
        $this->app->singleton(FastExcelAbsensiExport::class, function ($app) {
            return new FastExcelAbsensiExport();
        });
        
        // Register FastExcelSiswaImport as a singleton
        $this->app->singleton(FastExcelSiswaImport::class, function ($app) {
            return new FastExcelSiswaImport();
        });
        
        // Log that the service provider has loaded
        \Illuminate\Support\Facades\Log::info('FastExcelAbsensiExport class loaded successfully');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Create necessary directories for template storage
        $this->ensureTemplateDirectoriesExist();
    }
    
    /**
     * Ensure template directories exist
     */
    protected function ensureTemplateDirectoriesExist(): void
    {
        $directories = [
            storage_path('app/public/templates'),
            storage_path('app/temp')
        ];
        
        foreach ($directories as $directory) {
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
                \Illuminate\Support\Facades\Log::info("Created directory: {$directory}");
            }
        }
    }
}
