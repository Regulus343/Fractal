<?php namespace Regulus\Fractal;

use Regulus\Fractal\Facade as Fractal;

/*
|--------------------------------------------------------------------------
| Application & Route Filters
|--------------------------------------------------------------------------
|
| Below you will find the "before" and "after" events for the application
| which may be used to do any work before or after a request into your
| application. Here you may also register your custom route filters.
|
*/

use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;

use Auth;
use Regulus\SolidSite\Facade as Site;

$baseUri = config('cms.base_uri');

$authFilters = config('cms.auth_filters');
Route::filter('roles', function() use ($baseUri, $authFilters)
{
	$uriSegments = explode('/', Request::path());

	foreach ($authFilters as $filterUri => $allowedRoles)
	{
		foreach ($uriSegments as $prefixUri => $suffixUri)
		{
			if ($prefixUri == $filterUri || $prefixUri.'/'.$suffixUri == $filterUri)
			{
				if (!Auth::is($allowedRoles))
					return Redirect::to(Fractal::url('/login'))
						->with('messages', ['error' => Fractal::trans('messages.errors.unauthorized')]);
			}
		}
	}
});

if (!App::runningInConsole() && is_array($authFilters))
{
	foreach ($authFilters as $uri => $allowedRoles)
	{
		$subRoutes = true;
		if (substr($uri, 0, 1) == "[" && substr($uri, -1) == "]")
			$subRoutes = false;

		if ($uri == "*")
			$subRoutes = false;

		if ($uri == "X")
			$uri = "";

		$uri = str_replace('[', '', str_replace(']', '', $uri));

		$fullUri = $baseUri;
		if ($uri != "")
			$fullUri .= '/'.$uri;

		Route::when($fullUri, 'roles');

		if ($subRoutes)
			Route::when($baseUri.'/'.$uri.'/*', 'roles');
	}
}