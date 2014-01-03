<?php namespace Regulus\Fractal;

use \BaseController;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;

use Aquanode\Formation\Formation as Form;
use Regulus\ActivityLog\Activity;
use Regulus\SolidSite\SolidSite as Site;

class AccountController extends BaseController {

	public function __construct()
	{
		$section = "Account";
		Site::setMulti(array('section', 'title'), $section);

		Fractal::setViewsLocation('account');
	}

	public function getIndex()
	{
		$section = "Account";
		Site::setMulti(array('section', 'title'), $section);

		Fractal::setViewsLocation('core');

		return View::make(Fractal::view('home'));
	}

}