<?php namespace Regulus\Fractal;

use \BaseController;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL;

use Aquanode\Formation\Formation as Form;
use Regulus\ActivityLog\Activity;
use Regulus\TetraText\TetraText as Format;
use Regulus\SolidSite\SolidSite as Site;

use Regulus\Identify\User as User;
use Regulus\Identify\Role as Role;

class UsersController extends BaseController {

	public function __construct()
	{
		Site::set('section', 'Content');
		$section = "Users";
		Site::setMulti(array('section', 'subSection', 'title'), $section);

		Fractal::setViewsLocation('users');
	}

	public function index()
	{
		return View::make(Fractal::view('list'));
	}

	public function create()
	{
		Site::set('title', 'Create User');
		Site::set('wysiwyg', true);

		Form::setDefaults(array('active' => true));

		return View::make(Fractal::view('form'));
	}

	public function store()
	{
		Site::set('title', 'Create User');
		Site::set('wysiwyg', true);

		$rules = array(
			'username' => array('required', 'alpha_dash', 'min:3', 'unique:auth_users,username'),
			'email'    => array('required', 'email'),
			'roles'    => array('required'),
			'password' => array('required', 'confirmed'),
		);

		if (Fractal::getSetting('Require Unique Email Addresses'))
			$rules['email'][] = 'unique:auth_users,email';

		$minPasswordLength = Fractal::getSetting('Minimum Password Length');
		if ($minPasswordLength)
			$rules['password'][] = 'min:'.$minPasswordLength;

		Form::setValidationRules($rules);

		$messages = array();
		if (Form::validated()) {
			$messages['success'] = Lang::get('fractal::messages.successUpdated', array('item' => Format::a('user')));

			$user = new User;
			$user->fill(Input::except('csrf_token', 'roles', 'password', 'password_confirmation'));
			$user->password = Hash::make(Input::get('password'));
			$user->save();

			$user->roles()->sync(Input::get('roles'));

			return Redirect::to(Fractal::uri('users'))
				->with('messages', $messages);
		} else {
			$messages['error']   = Lang::get('fractal::messages.errorGeneral');
		}

		return View::make(Fractal::view('form'))
			->with('update', false)
			->with('messages', $messages);
	}

	public function edit($username)
	{
		$user = Fractal::userByUsername($username);
		if (empty($user))
			return Redirect::to(Fractal::uri('users'))
				->with('messages', array('error' => Lang::get('fractal::messages.errorNotFound', array('item' => 'user'))));

		Site::set('title', $user->username.' (User)');
		Site::set('titleHeading', 'Update User: <strong>'.Format::entities($user->username).'</strong>');
		Site::set('wysiwyg', true);

		Form::setDefaults($user, array('roles' => 'id'));

		return View::make(Fractal::view('form'))->with('update', true);
	}

	public function update($username)
	{
		$user = Fractal::userByUsername($username);
		if (empty($user))
			return Redirect::to(Fractal::uri('users'))
				->with('messages', array('error' => Lang::get('fractal::messages.errorNotFound', array('item' => 'user'))));

		Site::set('title', $user->username.' (User)');
		Site::set('titleHeading', 'Update User: <strong>'.Format::entities($user->username).'</strong>');
		Site::set('wysiwyg', true);

		$rules = array(
			'username' => array('required', 'alpha_dash', 'min:3', 'unique:auth_users,username,'.$user->id),
			'email'    => array('required', 'email'),
			'roles'    => array('required'),
		);

		if (Fractal::getSetting('Require Unique Email Addresses'))
			$rules['email'][] = 'unique:auth_users,email,'.$user->id;

		$minPasswordLength = Fractal::getSetting('Minimum Password Length');
		if ($minPasswordLength)
			$rules['password'][] = 'min:'.$minPasswordLength;

		Form::setValidationRules($rules);

		$messages = array();
		if (Form::validated()) {
			$messages['success'] = Lang::get('fractal::messages.successUpdated', array('item' => Format::a('user')));

			$user->fill(Input::except('csrf_token', 'roles'));
			$user->roles()->sync(Input::get('roles'));
			$user->save();

			return Redirect::to(Fractal::uri('users'))
				->with('messages', $messages);
		} else {
			$messages['error']   = Lang::get('fractal::messages.errorGeneral');
		}

		return View::make(Fractal::view('form'))
			->with('update', true)
			->with('messages', $messages);
	}

	public function ban($userID)
	{
		$result = array(
			'resultType' => 'Error',
			'message'    => Lang::get('fractal::messages.errorGeneral'),
		);

		$user = \User::find($userID);
		if (empty($user))
			return $result;

		if ($user->banned)
			return $result;

		$user->banned    = true;
		$user->banned_at = date('Y-m-d H:i:s');
		$user->save();

		Activity::log(array(
			'contentID'   => $user->id,
			'contentType' => 'User',
			'description' => 'Banned a User',
			'details'     => 'Username: '.$user->username,
		));

		$result['resultType'] = "Success";
		$result['message']    = Lang::get('fractal::messages.successBanned', array('item' => 'a user'));
		return $result;
	}

	public function unban($userID)
	{
		$result = array(
			'resultType' => 'Error',
			'message'    => Lang::get('fractal::messages.errorGeneral'),
		);

		$user = \User::find($userID);
		if (empty($user))
			return $result;

		if (!$user->banned)
			return $result;

		$user->banned    = false;
		$user->banned_at = "0000-00-00 00:00:00";
		$user->save();

		Activity::log(array(
			'contentID'   => $user->id,
			'contentType' => 'User',
			'description' => 'Unbanned a User',
			'details'     => 'Username: '.$user->username,
		));

		$result['resultType'] = "Success";
		$result['message']    = Lang::get('fractal::messages.successUnbanned', array('item' => 'a user'));
		return $result;
	}

}