<?php namespace Regulus\Fractal;

use \BaseController;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;

use Aquanode\Formation\Formation as Form;
use Regulus\ActivityLog\Activity;
use Regulus\Identify\User as User;
use Regulus\SolidSite\SolidSite as Site;

class AuthController extends BaseController {

	public function __construct()
	{
		$section = "Log In";
		Site::setMulti(array('section', 'title'), $section);

		Fractal::setViewsLocation('auth');
	}

	public function login()
	{
		//check if an active session already exists
		if (Fractal::auth())
			return Redirect::to(Fractal::uri('account'))->with('messages', array('error' => Lang::get('fractal::messages.errorAlreadyLoggedIn')));

		//add a default username if it is set through a session variable
		if (!is_null(Session::get('username'))) {
			Form::setDefaults(array('username' => Session::get('username')));
			Session::forget('username');
		}

		//set form validation rules
		$rules = array(
			'username' => array('required', 'alpha_dash'),
			'password' => array('required', 'min:'.Fractal::getSetting('Minimum Password Length', 8)),
		);
		Form::setValidationRules($rules);

		$messages = array();

		//login form has been submitted
		if (Auth::attempt(array('username' => trim(Input::get('username')), 'password' => Input::get('password')))) {
			$user = Auth::user();

			Activity::log(array(
				'description' => 'Logged In',
				'details'     => 'Username: '.$user->username,
			));

			$returnUri = Session::get('returnUri');
			if (is_null($returnUri))
				$returnUri = Fractal::uri();

			Session::forget('returnUri');

			return Redirect::to($returnUri)->with('messages', array(
				'success' => Lang::get('fractal::messages.successLoggedIn', array('website' => Site::name(), 'user' => $user->getName()))
			));
		} else {
			if ($_POST) {
				$messages['error'] = Lang::get('fractal::messages.errorLogIn');

				Activity::log(array(
					'description' => 'Attempted to Log In',
					'details'     => 'Username: '.trim(Input::get('username')),
				));
			}
		}

		return View::make(Fractal::view('login'))->with('messages', $messages);
	}

	public function logout()
	{
		$user = Auth::user();

		Auth::logout();

		Activity::log(array(
			'description' => 'Logged Out',
			'details'     => 'Username: '.$user->username,
		));

		//set username session variable for easy logging back in
		Session::set('username', $user->username);

		return Redirect::to(Fractal::uri('login'))->with('messages', array('success' => Lang::get('fractal::messages.successLoggedOut')));
	}

	public function activate($userId = '', $code = '')
	{
		if (Auth::activate($userId, $code)) {
			$user = User::find($userId);

			Activity::log(array(
				'description' => 'Account Activated',
				'details'     => 'Username: '.$user->username,
			));

			return Redirect::to(Fractal::uri('login'))
				->with('messageSuccess', 'Your account has been successfully activated. You may now log in.')
				->with('username', $user->username);
		} else {
			$user = User::find($userId);
			if (!empty($user) && $user->active) {
				return Redirect::to(Fractal::uri('login'))
					->with('messageError', 'Your account has already been activated. You may log in below.');
			} else {
				return Redirect::to(Fractal::uri('login'))
					->with('messageError', 'Something went wrong with your attempt to activate your account.')
					->with('messageErrorSub', 'Please try the link in your email again. If you continue to have problems, please <a href="mailto:'.Site::get('email').'">email the webmaster</a>.');
			}
		}
	}

	//reset password step 1 of 2 (forgot password)
	public function forgotPassword()
	{
		Site::set('title', 'Reset Password');

		$rules = array('username' => 'required');
		Form::setValidationRules($rules);

		$messages = array();

		if (Form::validated()) {
			$username = trim(Input::get('username'));
			$user = User::getByUsernameOrEmail($username);

			if (!empty($user)) {
				$user->resetPasswordCode($username);

				Session::set('username', $user->username);

				Activity::log(array(
					'description' => 'Reset Password Credentials Successfully Requested',
					'details'     => 'Username: '.$user->username,
				));
			} else {
				Session::set('username', trim(Input::get('username')));

				Activity::log(array(
					'description' => 'Reset Password Credentials Erroneously Requested',
					'details'     => 'Username: '.trim(Input::get('username')),
				));
			}

			//even if user doesn't exist, suggest the user does exist to prevent someone from using this function to find usernames
			return Redirect::to(Fractal::uri('login'))->with('messages', array(
				'success' => Lang::get('fractal::messages.successForgotPassword')
			));
		} else {
			if ($_POST)
				$messages['error'] = Lang::get('fractal::messages.errorGeneral');
		}

		return View::make(Fractal::view('forgot_password'))->with('messages', $messages);
	}

	//reset password step 2 of 2
	public function resetPassword($id = 0, $code = '')
	{
		if (!$id || $code == "")
			return Redirect::to(Fractal::uri('forgot-password'))->with('messages', array(
				'error' => Lang::get('fractal::messages.errorResetPasswordInvalidUri')
			));

		$user = User::getActiveById($id);
		if (empty($user) || $user->reset_password_code != $code)
			return Redirect::to(Fractal::uri('forgot-password'))->with('messages', array(
				'error' => Lang::get('fractal::messages.errorNotFound', array('item' => strtolower(Lang::get('fractal::labels.user'))))
			));

		Site::set('title', 'Reset Password');

		$rules = array(
			'new_password' => array('required', 'min:'.Fractal::getSetting('Minimum Password Length', 8), 'max:64', 'confirmed'),
		);
		Form::setValidationRules($rules);

		$messages = array();

		if (Form::validated()) {
			$user->updateAccount('password');

			$user->reset_password_code = "";
			$user->save();

			Session::set('username', $user->username);

			Activity::log(array(
				'description' => 'Reset Password',
				'details'     => 'Username: '.$user->username,
			));

			return Redirect::to(Fractal::uri('login'))
				->with('messages', array('success' => Lang::get('fractal::messages.successResetPassword')));
		} else {
			if ($_POST)
				$messages['error'] = Lang::get('fractal::messages.errorGeneral');
		}

		return View::make(Fractal::view('reset_password'))->with('messages', $messages);
	}

}