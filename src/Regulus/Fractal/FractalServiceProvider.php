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

		//load routes, filters, view composers, and settings files
		include __DIR__.'/../../routes.php';
		include __DIR__.'/../../filters.php';
		include __DIR__.'/../../view_composers.php';
		include __DIR__.'/../../settings.php';

		//load config for dependent packages
		if (Config::get('fractal::workbench')) {
			$pathPrefix = __DIR__.'/../../../vendor/';
		} else {
			$pathPrefix = __DIR__.'/../../../../../';
		}
		$this->package('regulus/solid-site', null, $pathPrefix.'regulus/solid-site/src');
		$this->package('regulus/tetra-text', null, $pathPrefix.'regulus/tetra-text/src');
		$this->package('aquanode/formation', null, $pathPrefix.'aquanode/formation/src');
		$this->package('aquanode/elemental', null, $pathPrefix.'aquanode/elemental/src');
		$this->package('aquanode/upstream', null, $pathPrefix.'aquanode/upstream/src');

		//add view namespace for Elemental
		View::addNamespace('elemental', $pathPrefix.'aquanode/elemental/src/views');

		//alias dependent classes
		$loader = \Illuminate\Foundation\AliasLoader::getInstance();
		$loader->alias('Fractal',   'Regulus\Fractal\Fractal');
		$loader->alias('Site',      'Regulus\SolidSite\SolidSite');
		$loader->alias('Format',    'Regulus\TetraText\TetraText');
		$loader->alias('Elemental', 'Aquanode\Elemental\Elemental');
		$loader->alias('HTML',      'Aquanode\Elemental\Elemental');
		$loader->alias('Form',      'Aquanode\Formation\Formation');

		//alias models
		$loader->alias('Menu',      'Regulus\Fractal\Menu');
		$loader->alias('Page',      'Regulus\Fractal\Page');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		//add the install command
		$this->app['fractal:install'] = $this->app->share(function($app)
		{
			return new Commands\InstallCommand($app);
		});

		$this->commands('fractal:install');
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}