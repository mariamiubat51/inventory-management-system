<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Http\Middleware\CatchUser;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Push middleware to all "web" routes
        app('router')->pushMiddlewareToGroup('web', CatchUser::class);

        // If you want to apply it to API routes too, uncomment this:
        // app('router')->pushMiddlewareToGroup('api', CatchUser::class);
    }
}
