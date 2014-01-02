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
		$settings = Setting::orderBy('category')->orderBy('display_order')->orderBy('name');
		if (!Site::developer())
			$settings->where('developer', false);

		$settings = $settings->get();

		$defaults = array();
		foreach ($settings as $setting) {
			$defaults['setting_'.$setting->id] = $setting->value;
		}
		Form::setDefaults($defaults);

		return View::make(Fractal::view('list'))->with('settings', $settings);
	}

}