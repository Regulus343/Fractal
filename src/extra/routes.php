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

use Regulus\Fractal\Models\Content\Page;
use Regulus\Fractal\Models\Blogs\Article;

$baseUri        = Config::get('fractal::baseUri');
$controllers    = Config::get('fractal::controllers');
$methods        = Config::get('fractal::controllerMethods');
$exportedRoutes = Config::get('fractal::routes');

/* Additional Routes for Defined Controller Methods */
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

/* Routes for Defined Standard Controllers */
if (isset($controllers['standard'])) {
	foreach ($controllers['standard'] as $controllerURI => $controller) {
		Route::controller($baseUri.'/'.$controllerURI, $controller);
	}
}
if (isset($controllers['standard']['home']))
	Route::get($baseUri, $controllers['standard']['home'].'@getIndex');

/* Routes for Defined Resource Controllers */
if (isset($controllers['resource'])) {
	foreach ($controllers['resource'] as $controllerURI => $controller) {
		Route::resource($baseUri.'/'.$controllerURI, $controller);
	}
}
if (isset($controllers['resource']['home']))
	Route::get($baseUri, $controllers['resource']['home'].'@getIndex');

/* Developer Route (executing route enables "developer mode" via "developer" session variable) */
Route::get($baseUri.'/developer/{off?}', 'Regulus\Fractal\Controllers\General\DashboardController@getDeveloper');

/* API Routes */
Route::controller($baseUri.'/api', 'Regulus\Fractal\Controllers\General\ApiController');

/* Authorization Routes */
Route::any($baseUri.'/login', Config::get('fractal::authController').'@login');
Route::get($baseUri.'/logout', Config::get('fractal::authController').'@logout');
Route::any($baseUri.'/forgot-password', Config::get('fractal::authController').'@forgotPassword');
Route::any($baseUri.'/reset-password/{id?}/{code?}', Config::get('fractal::authController').'@resetPassword');

Route::controller($baseUri.'/auth', Config::get('fractal::authController'));

/* Blog Routes */
if (Config::get('fractal::blogs.enabled'))
{
	$blogSubdomain  = Config::get('fractal::blogs.subdomain');
	$blogUri        = Config::get('fractal::blogs.baseUri');
	$blogController = Config::get('fractal::blogs.viewController');

	$group = [];
	if (is_string($blogSubdomain) && $blogSubdomain != "")
		$group['domain'] = str_replace('http://', $blogSubdomain.'.', str_replace('https://', $blogSubdomain.'.', Config::get('app.url')));

	if ($blogUri != false && is_null($blogUri) && $blogUri != "")
		$group['prefix'] = $blogUri;

	Route::group($group, function() use ($blogController, $exportedRoutes)
	{
		/* Exported Blog Articles Routes */
		if (is_array($exportedRoutes) && isset($exportedRoutes['blogArticles']) && is_array($exportedRoutes['blogArticles']))
		{
			$blogArticleMethod = $blogController.'@getArticle';

			foreach ($exportedRoutes['blogArticles'] as $blogArticle)
			{
				Route::get('{'.$blogArticle.'}', $blogArticleMethod);

				//allow routes to exclude dashes to make it easy to verbally communicate URLs
				if (strpos($blogArticle, '-'))
					Route::get('{'.str_replace('-', '', $blogArticle).'}', $blogArticleMethod);
			}
		}

		/* Blog Controller Routes */
		Route::controller('', $blogController);
	});
}

/* Media Routes */
if (Config::get('fractal::media.enabled'))
{
	$mediaSubdomain  = Config::get('fractal::media.subdomain');
	$mediaUri        = Config::get('fractal::media.baseUri');
	$mediaController = Config::get('fractal::media.viewController');

	$group = [];
	if (is_string($mediaSubdomain) && $mediaSubdomain != "")
		$group['domain'] = str_replace('http://', $mediaSubdomain.'.', str_replace('https://', $mediaSubdomain.'.', Config::get('app.url')));

	if ($mediaUri != false && is_null($mediaUri) && $mediaUri != "")
		$group['prefix'] = $mediaUri;

	Route::group($group, function() use ($mediaController, $exportedRoutes)
	{
		/* Exported Media Items Routes */
		if (is_array($exportedRoutes) && isset($exportedRoutes['mediaItems']) && is_array($exportedRoutes['mediaItems']))
		{
			$mediaItemMethod = $mediaController.'@getItem';

			foreach ($exportedRoutes['mediaItems'] as $mediaItem)
			{
				Route::get('{'.$mediaItem.'}', $mediaItemMethod);

				//allow routes to exclude dashes to make it easy to verbally communicate URLs
				if (strpos($mediaItem, '-'))
					Route::get('{'.str_replace('-', '', $mediaItem).'}', $mediaItemMethod);
			}
		}

		/* Media Controller Routes */
		Route::controller('', $mediaController);
	});
}

/* Exported Content Pages Routes */
$pageUri    = Config::get('fractal::pageUri');
$pageMethod = Config::get('fractal::pageMethod');

if (is_array($exportedRoutes) && isset($exportedRoutes['pages']) && is_array($exportedRoutes['pages']))
{
	foreach ($exportedRoutes['pages'] as $page)
	{
		Route::get(($page != 'x' ? '{'.$page.'}' : ''), $pageMethod);

		//allow routes to exclude dashes to make it easy to verbally communicate URLs
		if (strpos($page, '-'))
			Route::get('{'.str_replace('-', '', $page).'}', $pageMethod);
	}
}

/* Content Pages Routes */
if (!is_null($pageUri) && $pageUri != false && $pageUri == "")
{
	Route::get($pageUri.'/{slug}', $pageMethod);

	if (Config::get('fractal::useHomePageForRoot'))
		Route::get('', $pageMethod);
}