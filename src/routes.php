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

use Regulus\Fractal\Models\ContentPage;

$baseUri     = Config::get('fractal::baseUri');
$controllers = Config::get('fractal::controllers');
$methods     = Config::get('fractal::controllerMethods');

/* Setup Routes for Defined Standard Controllers */
if (isset($controllers['standard'])) {
	foreach ($controllers['standard'] as $controllerURI => $controller) {
		Route::controller($baseUri.'/'.$controllerURI, $controller);
	}
}
if (isset($controllers['standard']['home']))
	Route::get($baseUri, $controllers['standard']['home'].'@getIndex');

/* Setup Routes for Defined Resource Controllers */
if (isset($controllers['resource'])) {
	foreach ($controllers['resource'] as $controllerURI => $controller) {
		Route::resource($baseUri.'/'.$controllerURI, $controller);
	}
}
if (isset($controllers['resource']['home']))
	Route::get($baseUri, $controllers['resource']['home'].'@getIndex');

/* Setup Additional Routes for Defined Controller Methods */
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

/* Setup Developer Route (executing route enables "developer mode" via "developer" session variable) */
Route::get($baseUri.'/developer/{off?}', 'Regulus\Fractal\CoreController@getDeveloper');

/* Setup Authorization Routes */
Route::any($baseUri.'/login', Config::get('fractal::authController').'@login');
Route::get($baseUri.'/logout', Config::get('fractal::authController').'@logout');
Route::any($baseUri.'/forgot-password', Config::get('fractal::authController').'@forgotPassword');
Route::any($baseUri.'/reset-password/{id?}/{code?}', Config::get('fractal::authController').'@resetPassword');

Route::controller($baseUri.'/auth', Config::get('fractal::authController'));

/* Setup Website Content Pages Routes */
$pageUri    = Config::get('fractal::pageUri');
$pageMethod = Config::get('fractal::pageMethod');
if ($pageUri == "") {
	//ensure DB tables have been migrated first
	if (Config::get('fractal::migrated') && !App::runningInConsole()) {
		$pages = ContentPage::select('slug')->where('active', '=', true)->get();
		foreach ($pages as $page) {
			Route::get('{'.$page->slug.'}', $pageMethod);

			if (Config::get('fractal::useHomePageForRoot') && $page->slug == "home")
				Route::get('', $pageMethod);
		}
	}
} else {
	Route::get($pageUri.'/{slug}', $pageMethod);
	Route::get('', $pageMethod);
}