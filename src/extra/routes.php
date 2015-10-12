<?php namespace Regulus\Fractal;

use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\App;

use Auth;

use Regulus\Fractal\Models\Content\Page;
use Regulus\Fractal\Models\Blog\Article;

$domain = str_replace('https://', '', str_replace('http://', '', config('app.url')));

$subdomain = config('cms.subdomain');
if (is_string($subdomain) && $subdomain != "")
	$domain = $subdomain.'.'.$domain;

Route::group(['domain' => $domain], function()
{
	$baseUri = config('cms.base_uri');

	/* Authorization Routes */
	Route::any($baseUri.'/login', config('cms.auth_controller').'@login');
	Route::get($baseUri.'/logout', config('cms.auth_controller').'@logout');
	Route::get($baseUri.'/password', config('cms.auth_controller').'@getEmail');
	Route::post($baseUri.'/password', config('cms.auth_controller').'@postEmail');
	Route::get($baseUri.'/password/reset/{token}', config('cms.auth_controller').'@getReset');
	Route::post($baseUri.'/password/reset/{token}', config('cms.auth_controller').'@postReset');
	Route::get('password/reset/{token}', config('cms.auth_controller').'@getReset');
	Route::post('password/reset/{token}', config('cms.auth_controller').'@postReset');

	Route::controller($baseUri.'/auth', config('cms.auth_controller'));

	Route::group(['middleware' => 'auth.fractal'], function() use ($baseUri)
	{
		$controllers = config('cms.controllers');
		$methods     = config('cms.controller_methods');

		/* Additional Routes for Defined Controller Methods */
		foreach (['get', 'post'] as $type)
		{
			if (isset($methods[$type]))
			{
				foreach ($methods[$type] as $route => $method)
				{
					if ($type == "get")
						Route::get($baseUri.'/'.$route, $method);
					else
						Route::post($baseUri.'/'.$route, $method);
				}
			}
		}

		/* Routes for Defined Standard Controllers */
		if (isset($controllers['standard']))
		{
			foreach ($controllers['standard'] as $controllerURI => $controller) {
				Route::controller($baseUri.'/'.$controllerURI, $controller);
			}
		}

		if (isset($controllers['standard']['home']))
			Route::get($baseUri, $controllers['standard']['home'].'@getIndex');

		/* Routes for Defined Resource Controllers */
		if (isset($controllers['resource']))
		{
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
	});
});

/* Blog Routes */
if (config('blogs.enabled'))
{
	$blogSubdomain  = config('blogs.subdomain');
	$blogUri        = config('blogs.base_uri');
	$blogController = config('blogs.view_controller');

	$group = [];
	if (is_string($blogSubdomain) && $blogSubdomain != "")
		$group['domain'] = str_replace('http://', $blogSubdomain.'.', str_replace('https://', $blogSubdomain.'.', config('app.url')));

	if ($blogUri != false && is_null($blogUri) && $blogUri != "")
		$group['prefix'] = $blogUri;

	Route::group($group, function() use ($blogController)
	{
		/* Short Article Routes */
		Route::get('{slug}', $blogController.'@getArticle');

		/* Blog Controller Routes */
		Route::controller('', $blogController);
	});
}

/* Media Routes */
if (config('media.enabled'))
{
	$mediaSubdomain  = config('media.subdomain');
	$mediaUri        = config('media.base_uri');
	$mediaController = config('media.view_controller');

	$group = [];
	if (is_string($mediaSubdomain) && $mediaSubdomain != "")
		$group['domain'] = str_replace('http://', $mediaSubdomain.'.', str_replace('https://', $mediaSubdomain.'.', config('app.url')));

	if ($mediaUri != false && is_null($mediaUri) && $mediaUri != "")
		$group['prefix'] = $mediaUri;

	Route::group($group, function() use ($mediaController)
	{
		/* Short Media Items Routes */
		Route::get('{slug}', $mediaController.'@getItem');

		/* Media Controller Routes */
		Route::controller('', $mediaController);
	});
}

/* Content Pages Routes */
$pageUri    = config('cms.page_uri');
$pageMethod = config('cms.page_method');

if (!is_null($pageUri) && $pageUri != false && $pageUri != "")
	Route::get($pageUri.'/{slug}', $pageMethod);
else
	Route::get('{slug}', $pageMethod);

if (config('cms.use_home_page_for_root'))
	Route::get('', $pageMethod);