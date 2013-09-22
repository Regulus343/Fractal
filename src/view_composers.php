<?php namespace Regulus\Fractal;

/*
|--------------------------------------------------------------------------
| View Composers
|--------------------------------------------------------------------------
|
| The view composers for the CMS.
|
*/

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;

use Regulus\SolidSite\SolidSite as Site;
use Aquanode\Formation\Formation as Form;

use Regulus\Identify\User;
use Regulus\Identify\Role;

View::composer(Config::get('fractal::viewsLocation').'partials.messages', function($view)
{
	$sessionMessages = Session::get('messages');
	$view->with('sessionMessages', $sessionMessages);
});

View::composer(Config::get('fractal::viewsLocation').'core.home', function($view)
{
	$view->with('hideTitle', true);
});

View::composer(Config::get('fractal::viewsLocation').'menus.list', function($view)
{
	$menus = Menu::where('id', '>', 0);
	if (!Site::developer()) $menus->where('cms', '=', false);
	$menus = $menus->orderBy('cms')->orderBy('name')->get();
	$view->with('menus', $menus);
});

View::composer(Config::get('fractal::viewsLocation').'menus.form', function($view)
{
	$typeOptions = Form::simpleOptions(array('URI', 'Content Page'));
	$pageOptions = Form::prepOptions(Page::select('id', 'title')->orderBy('title')->get(), array('id', 'title'));
	$view
		->with('typeOptions', $typeOptions)
		->with('pageOptions', $pageOptions);
});

View::composer(Config::get('fractal::viewsLocation').'pages.list', function($view)
{
	$view->with('pages', Page::orderBy('title')->get());
});

View::composer(Config::get('fractal::viewsLocation').'users.list', function($view)
{
	$view->with('users', User::orderBy('id')->get());
});

View::composer(Config::get('fractal::viewsLocation').'users.roles.list', function($view)
{
	$view->with('roles', Role::orderBy('id')->get());
});