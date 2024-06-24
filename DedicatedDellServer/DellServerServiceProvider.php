<?php

namespace App\Services\DellServer\Providers;

use Illuminate\Support\ServiceProvider;

class DellServerServiceProvider extends ServiceProvider
{
    /**
     * Register routes (Routes)
     *
     * @return bool
     */
    protected $routes = true;

    /**
     * Register views (Resources/views)
     *
     * @return bool
     */
    protected $views = true;

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the service.
     *
     * @return void
     */
    public function boot()
    {
        // Boot any necessary components
    }
}
