<?php

namespace App\Compatibility;

use Illuminate\Support\ServiceProvider;

/**
 * Custom Excel service provider that works with Laravel 12
 */
class ExcelServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register PHPExcel
        $this->app->singleton('phpexcel', function($app) {
            return new \PHPExcel();
        });

        // Register Excel reader
        $this->app->singleton('excel.reader', function($app) {
            return new \Maatwebsite\Excel\Readers\LaravelExcelReader(
                $app->make('files'),
                $app->make('excel.identifier')
            );
        });

        // Register Excel writer
        $this->app->singleton('excel.writer', function($app) {
            return new \Maatwebsite\Excel\Writers\LaravelExcelWriter(
                $app->make('files'),
                $app->make('excel.identifier')
            );
        });

        // Register Excel identifier
        $this->app->singleton('excel.identifier', function($app) {
            return new \Maatwebsite\Excel\Classes\FormatIdentifier();
        });

        // Register View parser
        $this->app->singleton('excel.parsers.view', function($app) {
            return new \Maatwebsite\Excel\Parsers\ViewParser(
                $app->make('view')
            );
        });

        // Register the main Excel class
        $this->app->singleton('excel', function($app) {
            return new \Maatwebsite\Excel\Excel(
                $app->make('phpexcel'),
                $app->make('excel.reader'),
                $app->make('excel.writer'),
                $app->make('excel.parsers.view')
            );
        });

        // Register the facade
        $this->app->singleton('Maatwebsite\Excel\Facades\Excel', function($app) {
            return $app->make('excel');
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        // Nothing to boot
    }
}
