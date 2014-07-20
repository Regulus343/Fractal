<?php namespace Regulus\Fractal;

use \BaseController;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

use Aquanode\Formation\Formation as Form;
use Regulus\ActivityLog\Activity;
use Regulus\SolidSite\SolidSite as Site;
use Regulus\TetraText\TetraText as Format;

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
			$defaults[$setting->getFieldName()] = $setting->value;
		}
		Form::setDefaults($defaults);
		Form::setErrors();

		return View::make(Fractal::view('list'))->with('settings', $settings);
	}

	public function postIndex()
	{
		$values = Input::all();

		$settings = Setting::orderBy('category')->orderBy('display_order')->orderBy('name')->get();
		$labels   = array();
		$rules    = array();
		foreach ($settings as $setting) {
			if (Site::developer() || ! (bool) $setting->developer) {
				$labels[$setting->getFieldName()] = $setting->getLabel();

				if ($setting->rules != "")
					$rules[$setting->getFieldName()] = explode(', ', $setting->rules);
			}
		}
		Form::setLabels($labels);
		Form::setValidationRules($rules);

		$messages = array();
		if (Form::validated()) {
			$messages['success'] = Lang::get('fractal::messages.successUpdated', array('item' => Lang::get('fractal::labels.settings')));

			foreach ($settings as $setting) {
				if (isset($values[$setting->getFieldName()])) {
					$setting->value = $values[$setting->getFieldName()];
					$setting->save();
				}
			}

			Fractal::exportSettings($settings);

			Activity::log(array(
				'contentType' => 'Setting',
				'action'      => 'Update',
				'description' => 'Updated Settings',
			));
		} else {
			$messages['error'] = Lang::get('fractal::messages.errorGeneral');
		}

		return Redirect::to(Fractal::uri('settings'))
			->with('messages', $messages)
			->with('errors', Form::getErrors())
			->withInput();
	}

}