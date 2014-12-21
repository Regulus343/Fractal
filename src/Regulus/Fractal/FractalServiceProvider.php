<?php namespace Regulus\Fractal;

use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;

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
		$this->package('regulus/fractal');

		$exterminator = Config::get('fractal::exterminator');

		//load config for dependent packages
		$vendorPath = base_path().'/vendor/';

		$configPackages = [
			'regulus/activity-log',
			'regulus/identify',
			'regulus/elemental',
			'regulus/formation',
			'regulus/solid-site',
			'regulus/tetra-text',
			'regulus/upstream',
		];

		if ($exterminator)
			$configPackages[] = "regulus/exterminator";

		foreach ($configPackages as $configPackage) {
			$this->package($configPackage, null, $vendorPath.$configPackage.'/src');
		}

		//add view namespace for Elemental
		View::addNamespace('elemental', $vendorPath.'regulus/elemental/src/views');

		//add aliases for dependent classes
		$loader = \Illuminate\Foundation\AliasLoader::getInstance();

		$loader->alias('Fractal',   'Regulus\Fractal\Facade');
		$loader->alias('Auth',      'Regulus\Identify\Facade');
		$loader->alias('Elemental', 'Regulus\Elemental\Facade');
		$loader->alias('HTML',      'Regulus\Elemental\Facade');
		$loader->alias('Form',      'Regulus\Formation\Facade');
		$loader->alias('Site',      'Regulus\SolidSite\Facade');
		$loader->alias('Format',    'Regulus\TetraText\Facade');
		$loader->alias('Upstream',  'Regulus\Upstream\Facade');
		$loader->alias('Markdown',  'MaxHoffmann\Parsedown\ParsedownFacade');

		if ($exterminator)
			$loader->alias('Dbg', 'Regulus\Exterminator\Exterminator');

		//create "parsedown" singleton for Markdown parsing
		$this->app->singleton('parsedown', function(){
			return new \Parsedown;
		});

		//load routes, filters, view composers, and settings files
		if (Config::get('fractal::preload.filters'))
			include app_path().'/filters.php';

		if (Config::get('fractal::preload.routes'))
			include app_path().'/routes.php';

		$extraPath = __DIR__.'/../../extra/';

		include $extraPath.'filters.php';
		include $extraPath.'helpers.php';
		include $extraPath.'validation_rules.php';
		include $extraPath.'view_composers.php';
		include $extraPath.'routes.php';
		include $extraPath.'settings.php';
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		//bind Fractal
		$this->app->bind('fractal', function()
		{
			return new Fractal();
		});

		//add the install command
		$this->app['fractal:install'] = $this->app->share(function($app)
		{
			return new Commands\InstallCommand($app);
		});

		$this->commands('fractal:install');

		//register additional service providers
		$this->app->register('Regulus\Elemental\ElementalServiceProvider');
		$this->app->register('Regulus\Formation\FormationServiceProvider');
		$this->app->register('Regulus\SolidSite\SolidSiteServiceProvider');
		$this->app->register('Regulus\TetraText\TetraTextServiceProvider');
		$this->app->register('Regulus\Upstream\UpstreamServiceProvider');
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return [];
	}

}