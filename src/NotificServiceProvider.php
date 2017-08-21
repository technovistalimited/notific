<?php

namespace Technovistalimited\Notific;

use Illuminate\Support\ServiceProvider;

class NotificServiceProvider extends ServiceProvider
{
	protected $migrations = [
		'CreateNotificationTable' => '2017_05_10_174725_create_notifications_table'
	];


	/**
	 * Bootstrap the application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->config();
		$this->bindModels();
		$this->migration();
	}

	/**
	 * Register the application services.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->bindConfig();
	}

	/**
	 * Bind Notific config.
	 *
	 * @return void
	 */
	protected function bindConfig()
	{
		$this->app->singleton('notific.config', function () {
			return new Config();
		});
	}

	/**
	 * Publish and merge config file.
	 *
	 * @return void
	 */
	protected function config()
	{
		$this->publishes([
			__DIR__ .'/config/notific.php' => config_path('notific.php'),
		]);

		$this->mergeConfigFrom(__DIR__ .'/config/notific.php', 'notific');
	}

	/**
	 * Publish migration files.
	 *
	 * @return void
	 */
	protected function migration()
	{
		foreach ($this->migrations as $class => $file) {
			if (! class_exists($class)) {
				$this->publishMigration($file);
			}
		}
	}

	/**
	 * Publish a single migration file.
	 *
	 * @param string $filename
	 * @return void
	 */
	protected function publishMigration($filename)
	{
		$extension = '.php';
		$filename  = trim($filename, $extension).$extension;
		$stub      = __DIR__ .'/migrations/'. $filename;
		$target    = $this->getMigrationFilepath($filename);

		$this->publishes([$stub => $target], 'migrations');
	}

	/**
	 * Get the migration file path.
	 *
	 * @param string $filename
	 * @return string
	 */
	protected function getMigrationFilepath($filename)
	{
		if (function_exists('database_path')) {
			return database_path('/migrations/'. $filename);
		}
		return base_path('/database/migrations/'. $filename);
	}

	/**
	 * Bind the models.
	 *
	 * @return string
	 */
	protected function bindModels()
	{
		$this->app->bind([
			'Notific' => 'Technovistalimited\Notific\Models\NotificModel::class'
		]);
	}
}
