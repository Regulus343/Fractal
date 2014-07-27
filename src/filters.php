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

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;

use \Auth as Auth;
use \Site as Site;

$baseUri = Config::get('fractal::baseUri');

Route::filter('fractal-auth', function() use ($baseUri)
{
	$path        = Request::path();
	$uriSegments = explode('/', $path);
	$uriToCheck  = isset($uriSegments[1]) ? $uriSegments[1] : '';

	if (!in_array($uriToCheck, ['login', 'logout', 'activate', 'forgot-password', 'reset-password'])) {
		if (!Fractal::auth()) {
			Session::set('returnUri', $path);

			return Redirect::to($baseUri.'/login')
				->with('messages', array('error' => Lang::get('fractal::messages.errorLogInRequired')));
		}

		$cmsRoles = Fractal::getSetting('CMS Roles', 'admin');
		if (Auth::isNot($cmsRoles)) {
			if (Config::get('fractal::userRoleNoCmsAccessLogOut'))
				Auth::logout();

			return Redirect::to(Config::get('fractal::userRoleNoCmsAccessUri'));
		}
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