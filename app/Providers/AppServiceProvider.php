<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Must be a singleton: the middleware sets the active site on it and
        // everything downstream (helpers, drivers, views) reads it back.
        $this->app->singleton(\App\Services\SiteManager::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
