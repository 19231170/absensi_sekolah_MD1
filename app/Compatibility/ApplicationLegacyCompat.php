<?php

namespace Illuminate\Foundation;

/**
 * Add Laravel 4 compatibility method to Application
 */
trait ApplicationLegacyCompat
{
    /**
     * Register a shared binding in the container.
     * Provides backward compatibility for Laravel 4's share() method
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
