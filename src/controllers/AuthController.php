<?php namespace Regulus\Fractal;

use \BaseController;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;

use Aquanode\Formation\Formation as Form;
use Regulus\SolidSite\SolidSite as Site;
use Regulus\ActivityLog\Activity;

class AuthController extends BaseController {

	public function __construct()
	{
		$section = "Log In";
		Site::setMulti(array('section', 'title'), $section);

		Fractal::setViewsLocation('auth');
	}

	public function getLogin()
	{
		//check if an active session already exists
		if (Fractal::auth()) return Redirect::to('account')->with('messageError', 'You are already logged in.');

		Site::setMulti(array('section', 'title'), 'Log In');
		Site::addTrailItem('Log In', 'login');

		//add a default username if it is set through the URI
		if (!is_null(Session::get('username'))) Form::setDefaults(array('username' => Session::get('username')));

		return View::make(Fractal::view('login'));
	}

	public function postLogin()
	{
		//check if an active session already exists
		if (Fractal::auth()) return Redirect::to('account')->with('messages', array('error' => 'You are already logged in.'));

		Site::setMulti(array('section', 'title'), 'Log In');
		Site::addTrailItem('Log In', 'login');

		//set form validation rules
		$rules = array('username' => array('required', 'alpha_dash'),
					   'password' => array('required', 'min:'.Setting::value('Minimum Password Length', 8)));
		Form::setValidationRules($rules);

		//login form has been submitted
		if (Auth::attempt(array('username' => trim(Input::get('username')), 'password' => Input::get('password')))) {
			$user = Auth::user();

			Activity::log(array(
				'description' => 'Logged In',
				'details'     => 'Username: '.$user->username,
			));

			$returnUri = Session::get('returnUri');
			if (is_null($returnUri)) $returnUri = Fractal::uri();
			Session::forget('returnUri');

			return Redirect::to($returnUri)->with('messages', array('success' => 'Welcome back to '.Site::name().', <strong>'.$user->username.'</strong>.'));
		} else {
			$messages = array();
			if ($_POST) $messages['error'] = "Something went wrong. Please check your username and password and try again.";

			Activity::log(array(
				'description' => 'Attempted to Log In',
				'details'     => 'Username: '.trim(Input::get('username')),
			));

			return View::make(Fractal::view('login'))->with('messages', $messages);
		}
	}

	public function getLogout()
	{
		$user = Auth::user();

		Auth::logout();

		Activity::log(array(
			'description' => 'Logged Out',
			'details'     => 'Username: '.$user->username,
		));

		return Redirect::to(Fractal::uri('login'))->with('messages', array('success' => 'You have successfully logged out.'));
	}

	/*public function getSignup()
	{
		//check if an active session already exists
		if (!Auth::guest()) return Redirect::to('account')->with('messageInfo', 'You are already logged in.');

		Site::set('section', 'Sign Up');
		Site::set('title', 'Sign Up - Become a Liberty Alliance Member');
		Site::addTrailItem('Sign Up', 'signup');

		$rules = array(
			'username'                 => array('required', 'min:3', 'max:36', 'alpha_dash', 'unique:auth_users,username'),
			'email'                    => array('required', 'email', 'unique:auth_users,email'),
			'first_name'               => array('required'),
			'last_name'                => array('required'),
			'city'                     => array('required'),
			'region'		           => array('required'),
			'password'                 => array('required', 'min:8', 'max:36', 'confirmed'),
			'password_confirmation'    => array('required'),
			'statement'                => array('required'),
			'terms'                    => array('required'),
			'recaptcha_response_field' => array('required', 'recaptcha'),
		);
		Form::setValidationRules($rules);

		Form::setDefaults(array('listed' => true));

		$messages = array();
		$messagesError = array(
			'error'    => 'Something went wrong.',
			'errorSub' => 'Please correct any errors and try again.',
		);
		if (Form::validated()) {
			if (User::createAccount()) {
				return Redirect::to('login')
					->with('username', trim(Input::get('username')))
					->with('messageSuccess', 'You have successfully created an account.')
					->with('messageSuccessSub', 'Please check your email for account activation instructions.');
			} else {
				$messages = $messagesError;
			}
		} else {
			if ($_POST) $messages = $messagesError;
		}
		return View::make('auth.signup')->with('messages', $messages);
	}*/

	public function getActivate($userID = '', $code = '')
	{
		if (Auth::activate($userID, $code)) {
			$user = User::find($userID);
			return Redirect::to('login')
				->with('messageSuccess', 'Your account has been successfully activated. You may now log in.')
				->with('username', $user->username);
		} else {
			$user = User::find($userID);
			if (!empty($user) && $user->active) {
				return Redirect::to('login')
					->with('messageError', 'Your account has already been activated. You may log in below.');
			} else {
				return Redirect::to('login')
					->with('messageError', 'Something went wrong with your attempt to activate your account.')
					->with('messageErrorSub', 'Please try the link in your email again. If you continue to have problems, please <a href="mailto:'.Site::get('email').'">email the webmaster</a>.');
			}
		}
	}

	//reset password step 1 of 2 (forgot password)
	public function getForgotPassword()
	{
		Site::set('section', 'Log In');
		Site::set('title', 'Reset Password (Step 1/2)');
		Site::addTrailItem('Log In', 'login');
		Site::addTrailItem('Reset Password', 'forgot-password');

		$rules = array('username' => 'required');
		Form::setValidationRules($rules);

		$messages = array();

		if (Form::validated()) {

			$username = trim(Input::get('username'));
			$user = User::getByUsernameOrEmail($username);

			if (!empty($user)) {
				$user->resetPasswordCode($username);

				return Redirect::to('login')
					->with('username', $user->username)
					->with('messageSuccess', 'An email has been sent to your email address with further instructions on resetting your password.');
			} else {
				//username does not exist, but the system will act as though it does
				return Redirect::to('login')
					->with('messageSuccess', 'An email has been sent to your email address with further instructions on resetting your password.');
			}
		}
		return View::make('auth.forgot_password')
			->with('messages', $messages);
	}

	//reset password step 2 of 2
	public function getResetPassword($id = 0, $code = '')
	{
		if (!$id || $code == "")
			return Redirect::to('forgot-password')
				->with('messageError', 'To reset password, please click on the link that was sent to your email address.')
				->with('messageErrorSub', 'If you did not receive an email, please enter your email address below to resend it.');

		$user = User::getActiveByID($id);
		if (empty($user) || $user->reset_password_code != $code)
			return Redirect::to('forgot-password')->with('messageError', 'The user account does not exist.');

		Site::set('section', 'Log In');
		Site::set('title', 'Reset Password (Step 2/2)');
		Site::addTrailItem('Log In', 'login');
		Site::addTrailItem('Reset Password', 'reset-password/'.$id.'/'.$code);

		$rules = array(
			'new_password'              => array('required', 'min:8', 'max:36', 'confirmed'),
			'new_password_confirmation' => array('required'),
		);
		Form::setValidationRules($rules);

		$messages = array();

		if (Form::validated()) {

			$user->updateAccount('password');
			return Redirect::to('login')
				->with('username', $user->username)
				->with('messageSuccess', 'You have successfully reset your password.')
				->with('messageSuccessSub', 'You may now log into your account with your new password.');

		} else {
			if ($_POST) {
				$messages = array(
					'error'    => 'Something went wrong.',
					'errorSub' => 'Please correct any errors and try again.'
				);
			}
			return View::make('auth.reset_password')
				->with('messages', $messages);
		}
	}

}