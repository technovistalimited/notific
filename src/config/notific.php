<?php
/**
 * ---------------------------------------------------------------------
 * Notific Configuration
 *
 * Configure Notific and its APIs from this configuration. Change
 * and manage notific from this single file easily.
 * ---------------------------------------------------------------------
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Database Connection Name
    |--------------------------------------------------------------------------
    |
    | The database connection name to use for notifications, audit logs, etc.
    | This should match a connection defined in your application's
    | config/database.php 'connections' array.
    |
    | Set to null to use the application's default database connection.
    | If you define a connection name here, you should also define the
    | connection credentials below so the package can register the
    | connection at runtime.
    |
    */
    'connection' => env('NOTIFIC_DB_CONNECTION', null),

    /*
    |--------------------------------------------------------------------------
    | Database Connection Credentials
    |--------------------------------------------------------------------------
    |
    | Define the full database connection credentials here.
    | These values will be used to register the connection in the
    | application's database configuration at runtime via the service provider.
    |
    | This way, the package is self-contained and does NOT require the
    | consuming application to manually edit config/database.php.
    |
    | Only used when 'connection' above is set to a non-null value.
    | If 'connection' is null, these are ignored.
    |
    */
    'database' => [
        'driver'    => env('NOTIFIC_DB_DRIVER', 'mysql'),
        'host'      => env('NOTIFIC_DB_HOST', '127.0.0.1'),
        'port'      => env('NOTIFIC_DB_PORT', '3306'),
        'database'  => env('NOTIFIC_DB_DATABASE', 'notific_db'),
        'username'  => env('NOTIFIC_DB_USERNAME', 'root'),
        'password'  => env('NOTIFIC_DB_PASSWORD', ''),
        'charset'   => env('NOTIFIC_DB_CHARSET', 'utf8mb4'),
        'collation' => env('NOTIFIC_DB_COLLATION', 'utf8mb4_unicode_ci'),
        'prefix'    => env('NOTIFIC_DB_PREFIX', ''),
        'strict'    => env('NOTIFIC_DB_STRICT', true),
        'engine'    => env('NOTIFIC_DB_ENGINE', null),
        'options'   => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configure caching behavior for notifications.
    |
    */
    'cache' => [
        /**
         * Is Cache enabled.
         *
         * Whether or not the caching is enabled.
         * If you want to disable the caching, make it 'false' here.
         *
         * Default: true - enabled.
         */
        'is_cache' => true,

        /**
         * Cache Time.
         *
         * Time to store fetched notifications in cache,
         * to save some valuable resources.
         *
         * @since 0.2.0 Introduced.
         * @since 1.0.0 Modified to seconds since Laravel 5.8.
         *
         * Default: 10 * 60 = 10 minutes in seconds.
         */
        'cache_time' => 10 * 60, // in seconds.
    ],
];
