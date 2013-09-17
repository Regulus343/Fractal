<?php namespace Regulus\Fractal;

use Illuminate\Support\ServiceProvider;

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

		//add view namespace for Elemental
		View::addNamespace('elemental', __DIR__.'/../../../vendor/aquanode/elemental/src/views');

		//load config for dependent packages
		$this->app['config']->package('regulus/solid-site', __DIR__.'/../../../vendor/regulus/solid-site/src/config');
		$this->app['config']->package('regulus/tetra-text', __DIR__.'/../../../vendor/regulus/tetra-text/src/config');
		$this->app['config']->package('aquanode/elemental', __DIR__.'/../../../vendor/aquanode/elemental/src/config');
		$this->app['config']->package('aquanode/formation', __DIR__.'/../../../vendor/aquanode/formation/src/config');

		//alias dependent classes
		$loader = \Illuminate\Foundation\AliasLoader::getInstance();
		$loader->alias('Fractal', 'Regulus\Fractal\Fractal');
		$loader->alias('Site', 'Regulus\SolidSite\SolidSite');
		$loader->alias('Format', 'Regulus\TetraText\TetraText');
		$loader->alias('Elemental', 'Aquanode\Elemental\Elemental');
		$loader->alias('HTML', 'Aquanode\Elemental\Elemental');
		$loader->alias('Form', 'Aquanode\Formation\Formation');

		//alias models
		$loader->alias('Menu', 'Regulus\Fractal\Menu');
		$loader->alias('Page', 'Regulus\Fractal\Page');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{

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