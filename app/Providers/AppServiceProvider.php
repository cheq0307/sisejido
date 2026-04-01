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
        //
    }

    /**
     * Bootstrap any application services.
     */
public function boot(): void
{
    if (app()->environment('production')) {
        \URL::forceScheme('https');
        if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
            \URL::forceRootUrl('https://' . $_SERVER['HTTP_X_FORWARDED_HOST']);
        }
    }
 }
}