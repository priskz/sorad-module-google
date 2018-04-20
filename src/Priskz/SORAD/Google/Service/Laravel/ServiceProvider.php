<?php namespace Priskz\SORAD\Google\Service\Laravel;

use Config;
use Priskz\SORAD\Google\API\Laravel\Routes;
use Priskz\SORAD\Google\Service\Service;
use Priskz\SORAD\ServiceProvider\Laravel\AbstractRootServiceProvider as RootServiceProvider;

class ServiceProvider extends RootServiceProvider
{
    /**
     * @property string $providerKey
     */
	protected static $providerKey = 'google-root';

    /**
     * @property array $aggregates
     */
	protected $aggregates = [];

	/**
	 * Perform post-registration booting of services.
	 *
	 * @return void
	 */
	public function boot()
	{
		// Load Module Configurations
	    $this->publishes([
	    	realpath(__DIR__ . '/../..') . '/config/Laravel/google.php' => config_path('sorad/google.php'),
	    	realpath(__DIR__ . '/../..') . '/views' => resource_path('views/vendor/priskz'),
	    ]);

	    // Load Module Migrations
	    $this->loadMigrationsFrom(realpath(__DIR__ . '/../..') . '/migrations/Laravel');

	    // Load Routes
	    Routes::load();
	}

	/**
	 * Register Services.
	 *
	 * @return void
	 */
	protected function registerService()
	{
		$this->app->singleton($this->getProviderKey(), function($app)
		{
			return new Service($this->getProviderKey(), Config::get('sorad.google.client_secret'));
		});
	}
}