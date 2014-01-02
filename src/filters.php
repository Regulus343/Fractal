<?php namespace Regulus\Fractal;

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

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;

use Regulus\SolidSite\SolidSite as Site;

$baseUri = Config::get('fractal::baseUri');

Route::filter('fractal-auth', function() use ($baseUri)
{
	$path = Request::path();
	$uriSegments = explode('/', $path);
	if (!Fractal::auth() && end($uriSegments) != "login") {
		Session::set('returnUri', $path);

		return Redirect::to($baseUri.'/login')
			->with('messages', array('error' => Lang::get('fractal::messages.errorLogInRequired')));
	}
});

Route::when($baseUri, 'fractal-auth');
Route::when($baseUri.'/*', 'fractal-auth');

$authFilters = Config::get('fractal::authFilters');
Route::filter('roles', function() use ($baseUri, $authFilters)
{
	$uriSegments = explode('/', Request::path());
	foreach ($authFilters as $filterUri => $allowedRoles) {
		foreach ($uriSegments as $prefixUri => $suffixUri) {
			if ($prefixUri == $filterUri || $prefixUri.'/'.$suffixUri == $filterUri) {
				if (!Fractal::roles($allowedRoles))
					return Redirect::to($baseUri.'/login')
						->with('messages', array('error' => Lang::get('fractal::messages.errorUnauthorized')));
			}
		}
	}
});

foreach ($authFilters as $uri => $allowedRoles) {
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