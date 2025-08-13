<?php

namespace App\Compatibility;

use Illuminate\Foundation\Application as LaravelApplication;

/**
 * Extended Application class with legacy support
 */
class ExtendedApplication extends LaravelApplication
{
    /**
     * Register a shared binding in the container.
     *
     * @param  string  $abstract
     * @param  \Closure|string|null  $concrete
     * @return void
     */
    public function share($abstract, $concrete = null)
    {
        return $this->singleton($abstract, $concrete);
    }
}
