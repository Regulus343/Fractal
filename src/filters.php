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

$baseURI = Config::get('fractal::baseURI');

Route::filter('fractal-auth', function() use ($baseURI)
{
	$path = Request::path();
	$uriSegments = explode('/', $path);
	if (!Fractal::auth() && end($uriSegments) != "login") {
		Session::set('returnURI', $path);

		return Redirect::to($baseURI.'/login')
			->with('messages', array('error' => Lang::get('fractal::messages.errorLogInRequired')));
	}
});

Route::when($baseURI, 'fractal-auth');
Route::when($baseURI.'/*', 'fractal-auth');

$authFilters = Config::get('fractal::authFilters');
Route::filter('roles', function() use ($baseURI, $authFilters)
{
	$uriSegments = explode('/', Request::path());
	foreach ($authFilters as $filterURI => $allowedRoles) {
		foreach ($uriSegments as $prefixURI => $suffixURI) {
			if ($prefixURI == $filterURI || $prefixURI.'/'.$suffixURI == $filterURI) {
				if (!Fractal::roles($allowedRoles))
					return Redirect::to($baseURI.'/loginx')
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

	$fullURI = $baseURI;
	if ($uri != "")
		$fullURI .= '/'.$uri;

	Route::when($fullURI, 'roles');

	if ($subRoutes)
		Route::when($baseURI.'/'.$uri.'/*', 'roles');
}