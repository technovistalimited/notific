<?php
/**
 * ---------------------------------------------------------------------
 * Notific Configuration
 * ---------------------------------------------------------------------
 */

return [
	'cache' => [

		/**
		 * Is Cache enabled.
		 *
		 * Whether or not the caching is enabled.
		 * If you want to disable the  caching, make it 'false' here.
		 *
		 * Default: true - enabled.
		 * ---------------------------------------------------------------------
		 */
		'is_cache' => false,

		/**
		 * Cache Time.
		 *
		 * Time to store fetched notifications in cache,
		 * to save some valuable resources.
		 *
		 * Default: 10 minutes.
		 * ---------------------------------------------------------------------
		 */
		'cache_time' => 10, // in minutes

	],
];
