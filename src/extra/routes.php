<?php namespace Regulus\Fractal;

use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\App;

use Auth;

use Regulus\Fractal\Models\Content\Page;
use Regulus\Fractal\Models\Blog\Article;

Route::group(['middleware' => 'web'], function()
{
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

		Route::group(['middleware' => ['auth.fractal', 'auth.permissions']], function() use ($baseUri)
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
				foreach ($controllers['standard'] as $controllerUri => $controller)
				{
					$controllerArray = explode(':', $controller);
					$controller      = $controllerArray[0];

					$actions = [];
					$prefix  = "";
					if (count($controllerArray) >= 2)
						$prefix = $controllerArray[1].'.';

					foreach (get_class_methods($controller) as $method)
					{
						$routeName = str_slug(str_replace('get', '', str_replace('post', '', str_replace('any', '', $method))));

						$actions[$method] = $prefix.$routeName;
					}

					Route::controller($baseUri.'/'.$controllerUri, $controller, $actions);
				}
			}

			if (isset($controllers['standard']['home']))
			{
				$controllerArray = explode(':', $controllers['standard']['home']);
				$controller      = $controllerArray[0];

				Route::get($baseUri, $controller.'@getIndex');
			}

			/* Routes for Defined Resource Controllers */
			if (isset($controllers['resource']))
			{
				foreach ($controllers['resource'] as $controllerUri => $controller)
				{
					Route::resource($baseUri.'/'.$controllerUri, $controller);
				}
			}

			if (isset($controllers['resource']['home']))
				Route::get($baseUri, $controllers['resource']['home'].'@getIndex');

			/* Developer Route (executing route enables "developer mode" via "developer" session variable) */
			Route::get($baseUri.'/developer/{off?}', 'Regulus\Fractal\Controllers\General\DashboardController@getDeveloper');

			/* API Routes */
			$controller = "Regulus\Fractal\Controllers\General\ApiController";
			$actions    = [];
			$prefix     = "api.";
			foreach (get_class_methods($controller) as $method)
			{
				$routeName = str_slug(str_replace('get', '', str_replace('post', '', str_replace('any', '', $method))));

				$actions[$method] = $prefix.$routeName;
			}
			Route::controller($baseUri.'/api', $controller, $actions);
		});
	});

	$groupDefault = [
		'middleware' => ['auth.permissions'],
	];

	/* Blog Routes */
	if (config('blogs.enabled'))
	{
		$blogSubdomain  = config('blogs.subdomain');
		$blogUri        = config('blogs.base_uri');
		$blogController = config('blogs.view_controller');

		$group = $groupDefault;
		if (is_string($blogSubdomain) && $blogSubdomain != "")
			$group['domain'] = str_replace('http://', $blogSubdomain.'.', str_replace('https://', $blogSubdomain.'.', config('app.url')));

		if ($blogUri != false && is_null($blogUri) && $blogUri != "")
			$group['prefix'] = $blogUri;

		Route::group($group, function() use ($blogController)
		{
			/* Short Article Routes */
			Route::get('{slug}', ['as' => 'blogs.articles.public.view', 'uses' => $blogController.'@getArticle']);

			/* Blog Controller Routes */
			$actions = [];
			$prefix  = "blogs.articles.public.";
			foreach (get_class_methods($blogController) as $method)
			{
				$routeName = str_slug(str_replace('get', '', str_replace('post', '', str_replace('any', '', $method))));

				$actions[$method] = $prefix.$routeName;
			}

			Route::controller('', $blogController, $actions);
		});
	}

	/* Media Routes */
	if (config('media.enabled'))
	{
		$mediaSubdomain  = config('media.subdomain');
		$mediaUri        = config('media.base_uri');
		$mediaController = config('media.view_controller');

		$group = $groupDefault;

		if (is_string($mediaSubdomain) && $mediaSubdomain != "")
			$group['domain'] = str_replace('http://', $mediaSubdomain.'.', str_replace('https://', $mediaSubdomain.'.', config('app.url')));

		if ($mediaUri != false && is_null($mediaUri) && $mediaUri != "")
			$group['prefix'] = $mediaUri;

		Route::group($group, function() use ($mediaController)
		{
			/* Short Media Items Routes */
			Route::get('{slug}', ['as' => 'media.items.public.view', 'uses' => $mediaController.'@getItem']);

			/* Media Controller Routes */
			$actions = [];
			$prefix  = "media.items.public.";
			foreach (get_class_methods($mediaController) as $method)
			{
				$routeName = str_slug(str_replace('get', '', str_replace('post', '', str_replace('any', '', $method))));

				$actions[$method] = $prefix.$routeName;
			}

			Route::controller('', $mediaController, $actions);
		});
	}

	/* Content Pages Routes */
	$group = $groupDefault;

	$group['domain'] = str_replace('http://', '', str_replace('https://', '', config('app.url')));

	Route::group($group, function()
	{
		$pageUri    = config('cms.page_uri');
		$pageMethod = config('cms.page_method');

		if (!is_null($pageUri) && $pageUri != false && $pageUri != "")
			Route::get($pageUri.'/{slug}', ['as' => 'pages.view', 'uses' => $pageMethod]);
		else
			Route::get('{slug}', ['as' => 'pages.view', 'uses' => $pageMethod]);

		if (config('cms.use_home_page_for_root'))
			Route::get('', ['as' => 'home', 'uses' => $pageMethod]);
	});
});