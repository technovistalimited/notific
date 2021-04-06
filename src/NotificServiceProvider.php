<?php

namespace Technovistalimited\Notific;

use Illuminate\Support\ServiceProvider;

class NotificServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // ----------------------------
        // PUBLISH --------------------
        // ----------------------------

        // config
        $this->publishes([__DIR__ . '/config/notific.php' => config_path('notific.php')], 'notific');

        // migrations
        $this->publishes([__DIR__ .'/migrations/' => database_path('migrations')], 'notific');


        // ----------------------------
        // LOAD -----------------------
        // ----------------------------

        // config
        $this->mergeConfigFrom(__DIR__ . '/config/notific.php', 'notific');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app['config']->get('notific') === null) {
            $this->app['config']->set('notific', require __DIR__ .'/config/notific.php');
        }

        $this->app->singleton('notific', function ($app) {
            return new Notific();
        });
    }
}
