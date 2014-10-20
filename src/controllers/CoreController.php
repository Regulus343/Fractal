<?php namespace Regulus\Fractal\Controllers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

use Fractal;

use \Form;
use \Format;
use \Site;

class CoreController extends BaseController {

	public function __construct()
	{
		parent::__construct();

		Site::setMulti(['section', 'subSection'], 'Home');
		Site::set('title', Fractal::lang('labels.home'));

		Fractal::setViewsLocation('core');
	}

	public function getIndex()
	{
		Site::set('hideTitle', true);
		return View::make(Fractal::view('home'));
	}

	public function getDeveloper($off = false)
	{
		if ($off == "off") {
			Session::forget('developer');
			return Redirect::to(Fractal::url())->with('messages', array('info' => Fractal::lang('messages.developerModeDisabled')));
		} else {
			Site::setDeveloper();
			return Redirect::to(Fractal::url())->with('messages', array('info' => Fractal::lang('messages.developerModeEnabled')));
		}
	}

}