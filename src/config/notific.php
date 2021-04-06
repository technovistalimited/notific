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
