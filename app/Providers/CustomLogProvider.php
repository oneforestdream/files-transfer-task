<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class CustomLogProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('custom.log', function () {
            return new \App\Facades\ReminderMailer;
        });
    }
}
