<?php

namespace App\Compatibility;

use Illuminate\Foundation\Application;

/**
 * Extends Laravel's Application to provide backward compatibility for older packages
 */
class LegacyAppExtension extends Application
{
    /**
     * Register a shared binding in the container.
     * Provides backward compatibility with Laravel 4's share() method
     *
     * @param  string  $abstract
     * @param  \Closure|string|null  $concrete
     * @return void
     */
    public function share($abstract, $concrete = null)
    {
        $this->singleton($abstract, $concrete);
    }
}
