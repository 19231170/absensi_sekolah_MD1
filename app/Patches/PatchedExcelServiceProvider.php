<?php

namespace App\Patches;

use Maatwebsite\Excel\ExcelServiceProvider as BaseServiceProvider;

/**
 * Patched version of Maatwebsite\Excel\ExcelServiceProvider
 * to fix compatibility with modern Laravel versions
 */
class PatchedExcelServiceProvider extends BaseServiceProvider
{
    /**
     * Bind PHPExcel classes
     * @return void
     */
    protected function bindPHPExcelClass()
    {
        // Set object
        $me = $this;

        // Bind the PHPExcel class
        $this->app->singleton('phpexcel', function($app) use($me) {
            // Set locale
            $me->setLocale();

            // Set the caching settings
            $me->setCacheSettings();

            // Init phpExcel
            return new \Maatwebsite\Excel\Classes\PHPExcel();
        });
    }

    /**
     * Bind writers
     * @return void
     */
    protected function bindReaders()
    {
        // Bind the laravel excel reader
        $this->app->singleton('excel.reader', function($app) {
            return new \Maatwebsite\Excel\Readers\LaravelExcelReader(
                $app['files'], 
                $app['excel.identifier']
            );
        });

        // Bind the html reader class
        $this->app->singleton('excel.readers.html', function($app) {
            return new \Maatwebsite\Excel\Readers\Html();
        });
    }

    /**
     * Bind parsers
     * @return void
     */
    protected function bindParsers()
    {
        // Bind the view parser
        $this->app->singleton('excel.parsers.view', function($app) {
            return new \Maatwebsite\Excel\Parsers\ViewParser($app['excel.readers.html']);
        });
    }

    /**
     * Bind writers
     * @return void
     */
    protected function bindWriters()
    {
        // Bind the excel writer
        $this->app->singleton('excel.writer', function($app) {
            return new \Maatwebsite\Excel\Writers\LaravelExcelWriter(
                $app->make('Response'), 
                $app['files'], 
                $app['excel.identifier']
            );
        });
    }

    /**
     * Bind Excel class
     * @return void
     */
    protected function bindExcel()
    {
        // Bind the Excel class and inject its dependencies
        $this->app->singleton('excel', function($app) {
            $excel = new \Maatwebsite\Excel\Excel(
                $app['phpexcel'], 
                $app['excel.reader'], 
                $app['excel.writer']
            );
            
            // For older versions of the package, the view parser was passed as fourth parameter
            // In newer versions, we would use setViewParser, but this version doesn't have that method
            // We'll just create the Excel object with the three required parameters
            
            return $excel;
        });
    }

    /**
     * Bind other classes
     * @return void
     */
    protected function bindClasses()
    {
        // Bind the format identifier
        $this->app->singleton('excel.identifier', function($app) {
            return new \Maatwebsite\Excel\Classes\FormatIdentifier($app['files']);
        });
    }
    
    /**
     * Boot the package
     */
    public function boot()
    {
        // This package method isn't supported in newer Laravel
        // $this->package('maatwebsite/excel');
        
        // Instead, we'll manually load the configuration
        $this->loadConfigManually();
        
        // Set the autosizing settings
        $this->setAutoSizingSettings();
    }
    
    /**
     * Manually load config for newer Laravel versions
     */
    protected function loadConfigManually()
    {
        $configPath = __DIR__ . '/../../vendor/maatwebsite/excel/src/config/excel.php';
        
        if (file_exists($configPath)) {
            $this->mergeConfigFrom($configPath, 'excel');
        }
    }
}
