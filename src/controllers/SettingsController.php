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

class SettingsController extends BaseController {

	public function __construct()
	{
		$section = "Settings";
		Site::setMulti(array('section', 'titleHeading'), $section);
		Site::set('title', $section);

		Fractal::setViewsLocation('settings');
	}

	public function getIndex()
	{
		return View::make(Config::get('fractal::viewsLocation').'core.home');
	}

}