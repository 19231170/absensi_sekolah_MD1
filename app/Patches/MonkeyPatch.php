<?php

namespace Illuminate\Foundation;

// Only define this if it doesn't exist
if (!method_exists(Application::class, 'share')) {
    /**
     * This is a backward compatibility patch for Laravel 4's share() method
     * which is used by older packages like maatwebsite/excel v1.1
     * 
     * @param string $abstract
     * @param \Closure|string|null $concrete
     * @return void
     */
    function share($abstract, $concrete = null)
    {
        return $this->singleton($abstract, $concrete);
    }
}
