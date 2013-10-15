<?php namespace Regulus\Fractal;

use \BaseController;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL;

use Aquanode\Elemental\Elemental as HTML;
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
		$itemsPerPage = Fractal::getSetting('Items Listed Per Page', 20);
		$users        = User::orderBy('id')->paginate($itemsPerPage);

		$messages = array();
		if ($users->getTotal()) {
			$messages['success'] = Lang::get('fractal::messages.displayingItemsOfTotal', array(
				'start'  => $users->getFrom(),
				'end'    => $users->getTo(),
				'total'  => $users->getTotal(),
				'items'  => Format::pluralize(strtolower(Lang::get('fractal::labels.user')), $users->getTotal()),
			));
		} else {
			$messages['success'] = Lang::get('fractal::messages.displayingItems', array(
				'total'  => $users->getTotal(),
				'items'  => Format::pluralize(strtolower(Lang::get('fractal::labels.user')), $users->getTotal()),
			));
		}

		return View::make(Fractal::view('list'))
			->with('users', $users)
			->with('messages', $messages);
	}

	public function search()
	{
		$itemsPerPage = Fractal::getSetting('Items Listed Per Page', 20);
		$terms        = Input::get('search');
		$likeTerms    = '%'.$terms.'%';
		$page         = !is_null(Input::get('page')) ? Input::get('page') : 1;
		$changingPage = (bool) Input::get('changing_page');
		$result       = array(
			'resultType' => 'Error',
			'message'    => Lang::get('fractal::messages.searchNoResults', array('terms' => $terms)),
		);

		DB::getPaginator()->setCurrentPage($page);

		$users = User::where('username', 'like', $likeTerms)
						->orWhere('first_name', 'like', $likeTerms)
						->orWhere('last_name', 'like', $likeTerms)
						->orWhere(DB::raw('concat_ws(\' \', first_name, last_name)'), 'like', $likeTerms)
						->orWhere('email', 'like', $likeTerms)
						->orderBy('id')
						->paginate($itemsPerPage);
		if (count($users)) {
			if ($changingPage) {
				$result['resultType'] = "Success";
				$result['message'] = Lang::get('fractal::messages.displayingItemsOfTotal', array(
					'start'  => $users->getFrom(),
					'end'    => $users->getTo(),
					'total'  => $users->getTotal(),
					'items'  => Format::pluralize(strtolower(Lang::get('fractal::labels.user')), $users->getTotal()),
				));
			} else {
				if ($terms != "") {
					$result['resultType'] = "Success";
					$result['message'] = Lang::get('fractal::messages.searchResults', array(
						'terms' => $terms,
						'total' => $users->count(),
						'items' => Format::pluralize(strtolower(Lang::get('fractal::labels.user')), $users->count()),
					));
				} else {
					$result['message'] = Lang::get('fractal::messages.searchNoTerms');
				}
			}
		} else {
			$users = User::orderBy('id')->paginate($itemsPerPage);
			if ($terms == "") $result['message'] = Lang::get('fractal::messages.searchNoTerms');
		}

		$result['table'] = HTML::table(Config::get('fractal::tables.users'), $users);

		return $result;
	}

	public function create()
	{
		Site::set('title', 'Create User');
		Site::set('wysiwyg', true);

		$defaults    = array('active' => true);
		$defaultRole = Role::where('default', '=', true)->first();

		if (!empty($defaultRole))
			$defaults['roles.'.$defaultRole->id] = $defaultRole->id;

		Form::setDefaults($defaults);

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
			$user->first_name = Format::name($user->first_name);
			$user->last_name  = Format::name($user->last_name);
			$user->password   = Hash::make(Input::get('password'));
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
			$user->first_name = Format::name($user->first_name);
			$user->last_name  = Format::name($user->last_name);
			$user->save();

			$user->roles()->sync(Input::get('roles'));

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
		$result['message']    = Lang::get('fractal::messages.successBanned', array('item' => '<strong>'.$user->username.'</strong>'));
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
		$result['message']    = Lang::get('fractal::messages.successUnbanned', array('item' => '<strong>'.$user->username.'</strong>'));
		return $result;
	}

}