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

		$workbench    = Config::get('fractal::workbench');
		$exterminator = Config::get('fractal::exterminator');

		//load config for dependent packages
		if ($workbench)
			$pathPrefix = __DIR__.'/../../../vendor/';
		else
			$pathPrefix = __DIR__.'/../../../../../';

		$configPackages = array(
			'regulus/fractal',
			'regulus/activity-log',
			'regulus/solid-site',
			'regulus/tetra-text',
			'aquanode/formation',
			'aquanode/elemental',
			'aquanode/upstream',
		);

		if ($exterminator)
			$configPackages[] = "regulus/exterminator";

		foreach ($configPackages as $configPackage) {
			$this->package($configPackage, null, $pathPrefix.$configPackage.'/src');
		}

		//add view namespace for Elemental
		View::addNamespace('elemental', $pathPrefix.'aquanode/elemental/src/views');

		//add aliases for dependent classes
		$loader = \Illuminate\Foundation\AliasLoader::getInstance();

		$loader->alias('Fractal',   'Regulus\Fractal\Facade');
		$loader->alias('Auth',      'Regulus\Identify\Identify');
		$loader->alias('Site',      'Regulus\SolidSite\SolidSite');
		$loader->alias('Format',    'Regulus\TetraText\TetraText');
		$loader->alias('Elemental', 'Aquanode\Elemental\Elemental');
		$loader->alias('HTML',      'Aquanode\Elemental\Elemental');
		$loader->alias('Form',      'Aquanode\Formation\Facade');
		$loader->alias('Markdown',  'MaxHoffmann\Parsedown\ParsedownFacade');

		if ($exterminator)
			$loader->alias('Dbg', 'Regulus\Exterminator\Exterminator');

		//load Formation
		$this->app['formation'] = $this->app->share(function($app) {
			return new \Aquanode\Formation\Formation($app['html'], $app['url'], $app['session.store']->getToken());
		});

		//create "parsedown" singleton for Markdown parsing
		$this->app->singleton('parsedown', function(){
			return new \Parsedown;
		});
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
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