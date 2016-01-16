<?php namespace Regulus\Fractal;

use Illuminate\Support\ServiceProvider;

use Illuminate\Foundation\AliasLoader;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;

use Regulus\Fractal\Libraries\LoadConfiguration;

class FractalServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->publishes([
			__DIR__.'/config/auth.routes.php'  => config_path('auth.routes.php'),
			__DIR__.'/config/blogs.php'        => config_path('blogs.php'),
			__DIR__.'/config/cms.php'          => config_path('cms.php'),
			__DIR__.'/config/media.php'        => config_path('media.php'),
			__DIR__.'/config/social.php'       => config_path('social.php'),
			__DIR__.'/config/tables.php'       => config_path('tables.php'),

			__DIR__.'/config/cms_settings.php' => config_path('exported/cms_settings.php'),
			__DIR__.'/config/menus.php'        => config_path('exported/menus.php'),

			__DIR__.'/resources/assets'        => assets_path('regulus/fractal'),
		]);

		$this->loadTranslationsFrom(__DIR__.'/resources/lang', 'fractal');

		$this->loadViewsFrom(__DIR__.'/resources/views', 'fractal');

		$extraPath  = __DIR__.'/extra/';
		$extraFiles = [
			'helpers',
			'routes',
			'settings',
			'validation_rules',
			'view_composers',
		];

		foreach ($extraFiles as $extraFile)
		{
			include $extraPath.$extraFile.'.php';
		}

		// load delayed configuration files
		$this->app->make('Regulus\Fractal\Libraries\LoadConfiguration')
			->loadConfigurationFiles($this->app, $this->app['config'], true);
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		// bind Fractal
		$this->app->singleton('Regulus\Fractal\Fractal', function()
		{
			return new Fractal;
		});

		// register additional service providers
		$this->app->register('Regulus\ActivityLog\ActivityLogServiceProvider');
		$this->app->register('Regulus\Elemental\ElementalServiceProvider');
		$this->app->register('Regulus\Formation\FormationServiceProvider');
		$this->app->register('Regulus\Identify\IdentifyServiceProvider');
		$this->app->register('Regulus\SolidSite\SolidSiteServiceProvider');
		$this->app->register('Regulus\TetraText\TetraTextServiceProvider');
		$this->app->register('Regulus\Upstream\UpstreamServiceProvider');
		$this->app->register('AlfredoRamos\ParsedownExtra\ParsedownExtraServiceProvider');

		// add aliases
		$loader = AliasLoader::getInstance();

		$loader->alias('Fractal',  'Regulus\Fractal\Facade');
		$loader->alias('HTML',     'Regulus\Elemental\Facade');
		$loader->alias('Form',     'Regulus\Formation\Facade');
		$loader->alias('Site',     'Regulus\SolidSite\Facade');
		$loader->alias('Format',   'Regulus\TetraText\Facade');
		$loader->alias('Upstream', 'Regulus\Upstream\Facade');
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return ['Regulus\Fractal\Fractal'];
	}

}