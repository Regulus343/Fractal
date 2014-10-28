<?php namespace Regulus\Fractal;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

use \Auth as Auth;

use Regulus\Fractal\Models\ContentPage;
use Regulus\Fractal\Models\BlogArticle;

$baseUri     = Config::get('fractal::baseUri');
$controllers = Config::get('fractal::controllers');
$methods     = Config::get('fractal::controllerMethods');

/* Set up Routes for Defined Standard Controllers */
if (isset($controllers['standard'])) {
	foreach ($controllers['standard'] as $controllerURI => $controller) {
		Route::controller($baseUri.'/'.$controllerURI, $controller);
	}
}
if (isset($controllers['standard']['home']))
	Route::get($baseUri, $controllers['standard']['home'].'@getIndex');

/* Set up Routes for Defined Resource Controllers */
if (isset($controllers['resource'])) {
	foreach ($controllers['resource'] as $controllerURI => $controller) {
		Route::resource($baseUri.'/'.$controllerURI, $controller);
	}
}
if (isset($controllers['resource']['home']))
	Route::get($baseUri, $controllers['resource']['home'].'@getIndex');

/* Set up Additional Routes for Defined Controller Methods */
foreach (array('get', 'post') as $type) {
	if (isset($methods[$type])) {
		foreach ($methods[$type] as $route => $method) {
			if ($type == "get") {
				Route::get($baseUri.'/'.$route, $method);
			} else {
				Route::post($baseUri.'/'.$route, $method);
			}
		}
	}
}

/* Set up Developer Route (executing route enables "developer mode" via "developer" session variable) */
Route::get($baseUri.'/developer/{off?}', 'Regulus\Fractal\Controllers\CoreController@getDeveloper');

/* Set up API Routes */
Route::controller($baseUri.'/api', 'Regulus\Fractal\Controllers\ApiController');

/* Set up Authorization Routes */
Route::any($baseUri.'/login', Config::get('fractal::authController').'@login');
Route::get($baseUri.'/logout', Config::get('fractal::authController').'@logout');
Route::any($baseUri.'/forgot-password', Config::get('fractal::authController').'@forgotPassword');
Route::any($baseUri.'/reset-password/{id?}/{code?}', Config::get('fractal::authController').'@resetPassword');

Route::controller($baseUri.'/auth', Config::get('fractal::authController'));

/* Set up Blog Routes */
if (Config::get('fractal::blog.enabled')) {
	$blogSubdomain  = Config::get('fractal::blog.subdomain');
	$blogUri        = Config::get('fractal::blog.baseUri');
	$blogController = Config::get('fractal::blog.viewController');

	$group = [];
	if ($blogSubdomain != false && !is_null($blogSubdomain) && $blogSubdomain != "")
		$group['domain'] = str_replace('http://', $blogSubdomain.'.', str_replace('https://', $blogSubdomain.'.', Config::get('app.url')));

	if ($blogUri != false && is_null($blogUri) && $blogUri != "")
		$group['prefix'] = $blogUri;

	Route::group($group, function() use ($blogController)
	{
		Route::controller('', $blogController);
	});
}

/* Set up Media Routes */
if (Config::get('fractal::media.enabled')) {
	$mediaSubdomain  = Config::get('fractal::media.subdomain');
	$mediaUri        = Config::get('fractal::media.baseUri');
	$mediaController = Config::get('fractal::media.viewController');

	$group = [];
	if ($mediaSubdomain != false && !is_null($mediaSubdomain) && $mediaSubdomain != "")
		$group['domain'] = str_replace('http://', $mediaSubdomain.'.', str_replace('https://', $mediaSubdomain.'.', Config::get('app.url')));

	if ($mediaUri != false && is_null($mediaUri) && $mediaUri != "")
		$group['prefix'] = $mediaUri;

	Route::group($group, function() use ($mediaController)
	{
		Route::controller('', $mediaController);
	});
}

/* Set up Website Content Pages Routes */
$pageUri    = Config::get('fractal::pageUri');
$pageMethod = Config::get('fractal::pageMethod');
if ($pageUri == "") {
	//ensure DB tables have been migrated first
	if (Config::get('fractal::migrated') && !App::runningInConsole()) {
		$pages = ContentPage::select(['slug', 'published_at']);

			$pages
				->whereNotNull('published_at')
				->where('published_at', '<=', date('Y-m-d H:i:s'));

		$pages = $pages->get();

		foreach ($pages as $page) {
			Route::get('{'.$page->slug.'}', $pageMethod);

			if (Config::get('fractal::useHomePageForRoot') && $page->slug == "home")
				Route::get('', $pageMethod);
		}
	}
} else {
	Route::get($pageUri.'/{slug}', $pageMethod);

	if (Config::get('fractal::useHomePageForRoot'))
		Route::get('', $pageMethod);
}