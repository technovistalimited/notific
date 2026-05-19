<?php

namespace Technovistalimited\Notific;

use Illuminate\Support\ServiceProvider;

class NotificServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/config/notific.php' => config_path('notific.php'),
        ], 'notific-config');

        // Publish migrations (modern Laravel 13.x way)
        // This auto-updates timestamps when published
        $this->publishesMigrations([
            __DIR__ . '/migrations' => database_path('migrations'),
        ], 'notific-migrations');

        // Merge default configuration
        $this->mergeConfigFrom(__DIR__ . '/config/notific.php', 'notific');
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        // Ensure config is loaded even if not published
        if (config('notific') === null) {
            config(['notific' => require __DIR__ . '/config/notific.php']);
        }

        // Register the database connection at runtime
        $this->registerDatabaseConnection();

        $this->app->singleton('notific', function ($app) {
            return new Notific();
        });
    }

    /**
     * Register the notific database connection at runtime.
     *
     * If the user has configured a custom connection name in config/notific.php,
     * this method registers the full connection credentials into the application's
     * database configuration so that DB::connection('notific') works without
     * the user manually editing config/database.php.
     *
     * @return void
     */
    protected function registerDatabaseConnection(): void
    {
        $connectionName = config('notific.connection');

        // If no custom connection is specified, use the default -- nothing to register
        if (empty($connectionName)) {
            return;
        }

        $dbConfig = config('notific.database');

        // Build the connection config array for Laravel
        $connectionConfig = [
            'driver'    => $dbConfig['driver'] ?? 'mysql',
            'host'      => $dbConfig['host'] ?? '127.0.0.1',
            'port'      => $dbConfig['port'] ?? '3306',
            'database'  => $dbConfig['database'] ?? 'notific_db',
            'username'  => $dbConfig['username'] ?? 'root',
            'password'  => $dbConfig['password'] ?? '',
            'charset'   => $dbConfig['charset'] ?? 'utf8mb4',
            'collation' => $dbConfig['collation'] ?? 'utf8mb4_unicode_ci',
            'prefix'    => $dbConfig['prefix'] ?? '',
            'strict'    => $dbConfig['strict'] ?? true,
            'engine'    => $dbConfig['engine'] ?? null,
        ];

        // Merge any additional PDO options
        if (!empty($dbConfig['options']) && is_array($dbConfig['options'])) {
            $connectionConfig['options'] = $dbConfig['options'];
        }

        // Register the connection into the application's database config
        // This makes DB::connection('notific') work without user editing database.php
        $connections = config('database.connections', []);
        $connections[$connectionName] = $connectionConfig;
        config(['database.connections' => $connections]);
    }
}
