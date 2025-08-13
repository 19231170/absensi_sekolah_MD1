 <?php

namespace App;

use Illuminate\Foundation\Application as LaravelApplication;

/**
 * Simple extension of Laravel's Application class to add the share() method
 * needed by older packages like maatwebsite/excel v1.x
 */
class Application extends LaravelApplication
{
    /**
     * Register a shared binding in the container.
     *
     * @param  string  $abstract
     * @param  \Closure|string|null  $concrete
     * @return mixed
     */
    public function share($abstract, $concrete = null)
    {
        return $this->singleton($abstract, $concrete);
    }
}
