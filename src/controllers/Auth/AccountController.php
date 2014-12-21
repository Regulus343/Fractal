<?php namespace Regulus\Fractal\Controllers\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;

use Fractal;

use Regulus\ActivityLog\Activity;
use \Form;
use \Format;
use \Site;

use Regulus\Fractal\Controllers\BaseController;

class AccountController extends BaseController {

	public function __construct()
	{
		parent::__construct();

		Fractal::setControllerPath($this);

		Site::setMulti(['section', 'subSection'], 'Account');
		Site::set('title', Fractal::lang('labels.account'));

		Fractal::setViewsLocation('account');

		Fractal::addTrailItem(Fractal::lang('labels.account'), Fractal::getControllerPath());
	}

	public function getIndex()
	{
		$user = Auth::user();
		Form::setDefaults($user);
		Form::setErrors();
		return View::make(Fractal::view('form'));
	}

	public function postIndex()
	{
		$user = Auth::user();
		Form::setDefaults($user);

		$rules = [
			'username' => ['required', 'alpha_dash', 'min:2', 'unique:auth_users,username,'.$user->id],
			'email'    => ['required', 'email'],
		];

		if (Fractal::getSetting('Require Unique Email Addresses'))
			$rules['email'][] = 'unique:auth_users,email,'.$user->id;

		if (Input::get('password') != "") {
			$rules['password'] = ['required', 'confirmed'];

			$minPasswordLength = Fractal::getSetting('Minimum Password Length');
			if ($minPasswordLength)
				$rules['password'][] = 'min:'.$minPasswordLength;
		}

		Form::setValidationRules($rules);

		$messages = [];
		if (Form::validated()) {
			$messages['success'] = Fractal::lang('messages.successUpdated', ['item' => Fractal::lang('labels.yourAccount')]);

			$user->fill(Input::except('csrf_token', 'password', 'password_confirmation'));
			$user->first_name = Format::name($user->first_name);
			$user->last_name  = Format::name($user->last_name);

			if (Input::get('password') != "")
				$user->password = Hash::make(Input::get('password'));

			$user->save();
		} else {
			$messages['error'] = Fractal::lang('messages.errorGeneral');
		}

		return Redirect::to(Fractal::uri('', true))
			->with('messages', $messages)
			->with('errors', Form::getErrors())
			->withInput();
	}

}