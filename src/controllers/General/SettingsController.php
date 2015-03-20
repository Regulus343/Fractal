<?php namespace Regulus\Fractal\Controllers\General;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

use Fractal;

use Regulus\Fractal\Models\General\Setting;

use Regulus\ActivityLog\Models\Activity;
use Form;
use Format;
use Site;

use Regulus\Fractal\Controllers\BaseController;

class SettingsController extends BaseController {

	public function __construct()
	{
		parent::__construct();

		Fractal::setControllerPath($this);

		Site::setMulti(['section', 'subSection'], 'Settings');
		Site::setTitle(Fractal::trans('labels.settings'));

		Fractal::setViewsLocation('settings');

		Fractal::addTrailItem(Fractal::trans('labels.settings'), Fractal::getControllerPath());
	}

	public function getIndex()
	{
		$settings = Setting::orderBy('category')->orderBy('display_order')->orderBy('name');
		if (!Site::developer())
			$settings->where('developer', false);

		$settings = $settings->get();
		$defaults = [];
		foreach ($settings as $setting)
		{
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
		$labels   = [];
		$rules    = [];
		foreach ($settings as $setting)
		{
			if (Site::developer() || ! (bool) $setting->developer)
			{
				$labels[$setting->getFieldName()] = $setting->getLabel();

				if ($setting->rules != "")
					$rules[$setting->getFieldName()] = explode(', ', $setting->rules);
			}
		}

		Form::setLabels($labels);
		Form::setValidationRules($rules);

		$messages = [];
		if (Form::validated())
		{
			$messages['success'] = Fractal::trans('messages.successUpdated', ['item' => Fractal::transLower('labels.settings')]);

			foreach ($settings as $setting)
			{
				if (isset($values[$setting->getFieldName()]))
				{
					$value = $values[$setting->getFieldName()];

					if ($setting->type == "List" && is_array($value))
						$value = implode(', ', $value);

					$setting->value = $value;

					$setting->save();
				}
			}

			Fractal::exportSettings($settings);

			Activity::log([
				'contentType' => 'Setting',
				'action'      => 'Update',
				'description' => 'Updated Settings',
			]);
		} else {
			$messages['error'] = Fractal::trans('messages.errorGeneral');
		}

		return Redirect::to(Fractal::uri('', true))
			->with('messages', $messages)
			->with('errors', Form::getErrors())
			->withInput();
	}

}