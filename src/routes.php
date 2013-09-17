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

use Illuminate\Support\Facades\Config;

$baseURI     = Config::get('fractal::baseURI');
$controllers = Config::get('fractal::controllers');

/* Setup Routes for Defined Standard Controllers */
if (isset($controllers['standard'])) {
	foreach ($controllers['standard'] as $controllerURI => $controller) {
		Route::controller($baseURI.'/'.$controllerURI, $controller);
	}
}
if (isset($controllers['standard']['home']))
	Route::get($baseURI, $controllers['standard']['home'].'@getIndex');

/* Setup Routes for Defined Resource Controllers */
if (isset($controllers['resource'])) {
	foreach ($controllers['resource'] as $controllerURI => $controller) {
		Route::resource($baseURI.'/'.$controllerURI, $controller);
	}
}
if (isset($controllers['resource']['home']))
	Route::get($baseURI, $controllers['resource']['home'].'@getIndex');

/* Setup Developer Route (executing route enables "developer mode" via "developer" session variable) */
Route::get($baseURI.'/developer', 'Regulus\Fractal\CoreController@getDeveloper');

/* Setup Authorization Routes */
Route::get($baseURI.'/login', Config::get('fractal::authController').'@getLogin');
Route::post($baseURI.'/login', Config::get('fractal::authController').'@postLogin');
Route::get($baseURI.'/logout', Config::get('fractal::authController').'@getLogout');

Route::controller($baseURI.'/auth', Config::get('fractal::authController'));

/* Setup Website Content Pages Routes */
$pageURI    = Config::get('fractal::pageURI');
$pageMethod = Config::get('fractal::pageMethod');
if ($pageURI == "") {
	$pages = Page::select('slug')->where('active', '=', true)->get();
	foreach ($pages as $page) {
		Route::get('{'.$page->slug.'}', $pageMethod);
	}
} else {
	Route::get($pageURI.'/{slug}', $pageMethod);
}