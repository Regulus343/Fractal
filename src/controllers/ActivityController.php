<?php namespace Regulus\Fractal;

use \BaseController;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL;

use Aquanode\Formation\Formation as Form;
use Regulus\TetraText\TetraText as Format;
use Regulus\SolidSite\SolidSite as Site;

class ActivityController extends BaseController {

	public function __construct()
	{
		Site::set('section', 'Users');

		$subSection = "User Activity";
		Site::setMulti(array('subSection', 'title'), $subSection);

		Fractal::setViewsLocation('core');
	}

	public function getIndex()
	{
		return View::make(Fractal::view('home'));
	}

	public function getDeveloper()
	{
		Site::setDeveloper();
		return Redirect::to(Fractal::url())->with('messages', array('info' => '<strong>Developer Mode</strong> enabled.'));
	}

}