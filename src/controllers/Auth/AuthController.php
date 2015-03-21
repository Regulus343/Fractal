<?php namespace Regulus\Fractal\Controllers\Auth;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;

use Fractal;

use Regulus\ActivityLog\Models\Activity;
use Auth;
use Form;
use Site;
use User;

use Regulus\Fractal\Controllers\BaseController;

class AuthController extends BaseController {

	public function __construct()
	{
		Site::setMulti(['section', 'subSection'], 'Log In');
		Site::setTitle(Fractal::trans('labels.log_in'));
		Site::set('hideSidebar', true);

		Fractal::setViewsLocation('auth');
	}

	public function login()
	{
		// check if an active session already exists
		if (Auth::check())
			return Redirect::to(Fractal::uri('account'))->with('messages', ['error' => Fractal::trans('messages.errors.already_logged_in')]);

		// add a default username if it is set through a session variable
		if (!is_null(Session::get('username')))
		{
			Form::setDefaults(['identifier' => Session::get('username')]);
			Session::forget('username');
		}

		// set form validation rules
		$rules = [
			'identifier' => ['required'],
			'password'   => ['required', 'min:'.Fractal::getSetting('Minimum Password Length', 8)],
		];
		Form::setValidationRules($rules);

		$messages = [];

		// login form has been submitted
		if (Auth::attempt(['identifier' => Input::get('identifier'), 'password' => Input::get('password')]))
		{
			$user = Auth::user();

			Activity::log([
				'action'      => 'Log In',
				'description' => 'Logged In',
				'details'     => 'Username: '.$user->username(),
			]);

			$returnUri = Session::get('returnUri');
			if (is_null($returnUri))
				$returnUri = Fractal::uri();

			Session::forget('returnUri');

			return Redirect::to($returnUri)->with('messages', [
				'success' => Fractal::trans('messages.success.logged_in', ['website' => Site::name(), 'user' => $user->getName()])
			]);
		} else {
			if ($_POST)
			{
				$messages['error'] = Fractal::trans('messages.errors.log_in');

				Activity::log([
					'action'      => 'Log In',
					'description' => 'Attempted to Log In',
					'details'     => 'Username: '.trim(Input::get('username')),
				]);
			}
		}

		return View::make(Fractal::view('login'))->with('messages', $messages);
	}

	public function logout()
	{
		$user = Auth::user();

		Auth::logout();

		if (!empty($user))
		{
			Activity::log([
				'action'      => 'Log Out',
				'description' => 'Logged Out',
				'details'     => 'Username: '.$user->username(),
			]);

			// set username session variable for easy logging back in
			Session::set('username', $user->name);
		}

		return Redirect::to(Fractal::uri('login'))->with('messages', ['success' => Fractal::trans('messages.success.logged_out')]);
	}

	public function activate($userId = '', $code = '')
	{
		if (Auth::activate($userId, $code))
		{
			$user = User::find($userId);

			Activity::log([
				'action'      => 'Activate',
				'description' => 'Account Activated',
				'details'     => 'Username: '.$user->username,
			]);

			return Redirect::to(Fractal::uri('login'))
				->with('messages', ['success' => Fractal::trans('messages.success.account_activated')])
				->with('username', $user->username);
		} else {
			$user = User::find($userId);
			if (!empty($user) && $user->active) {
				return Redirect::to(Fractal::uri('login'))
					->with('messages', ['info' => Fractal::trans('messages.info.account_already_activated')]);
			} else {
				return Redirect::to(Fractal::uri('login'))
					->with('messages', ['error' => Fractal::trans('messages.error.account_activation')]);
			}
		}
	}

	// reset password step 1 of 2 (forgot password)
	public function forgotPassword()
	{
		Site::setTitle(Fractal::trans('labels.reset_password'));

		$rules = ['username' => 'required'];
		Form::setValidationRules($rules);

		$messages = [];

		if (Form::validated())
		{
			$username = trim(Input::get('username'));
			$user = User::getByUsernameOrEmail($username);

			if (!empty($user))
			{
				$user->resetPasswordCode($username);

				Session::set('username', $user->username);

				Activity::log([
					'description' => 'Reset Password Credentials Successfully Requested',
					'details'     => 'Username: '.$user->username,
				]);
			} else {
				Session::set('username', trim(Input::get('username')));

				Activity::log([
					'description' => 'Reset Password Credentials Erroneously Requested',
					'details'     => 'Username: '.trim(Input::get('username')),
				]);
			}

			//even if user doesn't exist, suggest the user does exist to prevent someone from using this function to find usernames
			return Redirect::to(Fractal::uri('login'))->with('messages', [
				'success' => Fractal::trans('messages.success._forgot_password')
			]);
		} else {
			if ($_POST)
				$messages['error'] = Fractal::trans('messages.errors.general');
		}

		return View::make(Fractal::view('forgot_password'))->with('messages', $messages);
	}

	// reset password step 2 of 2
	public function resetPassword($id = 0, $code = '')
	{
		if (!$id || $code == "")
			return Redirect::to(Fractal::uri('forgot-password'))->with('messages', [
				'error' => Fractal::trans('messages.errors.reset_password_invalid_uri')
			]);

		$user = User::getActiveById($id);
		if (empty($user) || $user->reset_password_code != $code)
			return Redirect::to(Fractal::uri('forgot-password'))->with('messages', [
				'error' => Fractal::trans('messages.errors.not_found', ['item' => strtolower(Fractal::trans('labels.user'))])
			]);

		Site::setTitle(Fractal::trans('labels.reset_password'));

		$rules = [
			'new_password' => ['required', 'min:'.Fractal::getSetting('Minimum Password Length', 8), 'max:64', 'confirmed'],
		];
		Form::setValidationRules($rules);

		$messages = [];

		if (Form::validated())
		{
			$user->updateAccount('password');

			$user->reset_password_code = "";
			$user->save();

			Session::set('username', $user->username);

			Activity::log([
				'description' => 'Reset Password',
				'details'     => 'Username: '.$user->username,
			]);

			return Redirect::to(Fractal::uri('login'))
				->with('messages', ['success' => Fractal::trans('messages.success.reset_password')]);
		} else {
			if ($_POST)
				$messages['error'] = Fractal::trans('messages.errors.general');
		}

		return View::make(Fractal::view('reset_password'))->with('messages', $messages);
	}

}