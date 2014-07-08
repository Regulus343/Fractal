<?php namespace Regulus\Fractal;

use \BaseController;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

use Aquanode\Formation\Formation as Form;
use Regulus\ActivityLog\Activity;
use Regulus\SolidSite\SolidSite as Site;
use Regulus\TetraText\TetraText as Format;

use Regulus\Identify\User as User;
use Regulus\Identify\Role as Role;

class UsersController extends BaseController {

	public function __construct()
	{
		Site::set('section', 'Content');
		$section = "Users";
		Site::setMulti(array('section', 'subSection', 'title'), $section);

		//set content type and views location
		Fractal::setContentType('user', true);
	}

	public function index()
	{
		$data = Fractal::setupPagination('Users');

		$users = User::orderBy($data['sortField'], $data['sortOrder']);
		if ($data['sortField'] == "last_name") $users->orderBy('first_name', $data['sortOrder']);
		if ($data['sortField'] != "id")        $users->orderBy('id', 'asc');
		if ($data['terms'] != "") {
			$users->where(function($query) use ($data) {
				$query
					->where('username', 'like', $data['likeTerms'])
					->orWhere('first_name', 'like', $data['likeTerms'])
					->orWhere('last_name', 'like', $data['likeTerms'])
					->orWhere(DB::raw('concat_ws(\' \', first_name, last_name)'), 'like', $data['likeTerms'])
					->orWhere('email', 'like', $data['likeTerms']);
			});
		}
		$users = $users->paginate($data['itemsPerPage']);

		Fractal::setContentForPagination($users);

		$data     = Fractal::setPaginationMessage();
		$messages = Fractal::getPaginationMessageArray();

		if (!count($users))
			$users = User::orderBy($data['sortField'], $data['sortOrder'])->paginate($data['itemsPerPage']);

		$defaults = array(
			'search' => $data['terms']
		);
		Form::setDefaults($defaults);

		return View::make(Fractal::view('list'))
			->with('content', $users)
			->with('messages', $messages);
	}

	public function search()
	{
		$data = Fractal::setupPagination('Users');

		$users = User::orderBy($data['sortField'], $data['sortOrder']);
		if ($data['sortField'] == "last_name") $users->orderBy('first_name', $data['sortOrder']);
		if ($data['sortField'] != "id")        $users->orderBy('id', 'asc');
		if ($data['terms'] != "") {
			$users->where(function($query) use ($data) {
				$query
					->where('username', 'like', $data['likeTerms'])
					->orWhere('first_name', 'like', $data['likeTerms'])
					->orWhere('last_name', 'like', $data['likeTerms'])
					->orWhere(DB::raw('concat_ws(\' \', first_name, last_name)'), 'like', $data['likeTerms'])
					->orWhere('email', 'like', $data['likeTerms']);
			});
		}
		$users = $users->paginate($data['itemsPerPage']);

		Fractal::setContentForPagination($users);

		if (count($users)) {
			$data = Fractal::setPaginationMessage();
		} else {
			$data['content'] = User::orderBy($data['sortField'], $data['sortOrder'])->paginate($data['itemsPerPage']);
			if ($data['terms'] == "") $data['result']['message'] = Lang::get('fractal::messages.searchNoTerms');
		}

		$data['result']['pages']     = Fractal::getLastPage();
		$data['result']['tableBody'] = Fractal::createTable($data['content'], true);

		return $data['result'];
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
		Form::setErrors();

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
			$messages['success'] = Lang::get('fractal::messages.successCreated', array('item' => 'a user'));

			$user = new User;
			$user->fill(Input::except('csrf_token', 'roles', 'password', 'password_confirmation'));
			$user->first_name = Format::name($user->first_name);
			$user->last_name  = Format::name($user->last_name);
			$user->password   = Hash::make(Input::get('password'));
			$user->save();

			$user->roles()->sync(Input::get('roles'));

			Activity::log(array(
				'contentId'   => $user->id,
				'contentType' => 'User',
				'action'      => 'Create',
				'description' => 'Created a User',
				'details'     => 'Username: '.$user->username,
			));

			return Redirect::to(Fractal::uri('users'))
				->with('messages', $messages);
		} else {
			$messages['error'] = Lang::get('fractal::messages.errorGeneral');
		}

		return Redirect::to(Fractal::uri('users/create'))
			->with('messages', $messages)
			->with('errors', Form::getErrors())
			->withInput();
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
		Form::setErrors();

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
			$messages['success'] = Lang::get('fractal::messages.successUpdated', array('item' => 'a user'));

			$user->fill(Input::except('csrf_token', 'roles'));
			$user->first_name = Format::name($user->first_name);
			$user->last_name  = Format::name($user->last_name);
			$user->save();

			$user->roles()->sync(Input::get('roles'));

			Activity::log(array(
				'contentId'   => $user->id,
				'contentType' => 'User',
				'action'      => 'Update',
				'description' => 'Updated a User',
				'details'     => 'Username: '.$user->username,
			));

			return Redirect::to(Fractal::uri('users'))
				->with('messages', $messages);
		} else {
			$messages['error'] = Lang::get('fractal::messages.errorGeneral');

			return Redirect::to(Fractal::uri('users/'.$username.'/edit'))
				->with('messages', $messages)
				->with('errors', Form::getErrors())
				->withInput();
		}
	}

	public function ban($id)
	{
		$result = array(
			'resultType' => 'Error',
			'message'    => Lang::get('fractal::messages.errorGeneral'),
		);

		$user = \User::find($id);
		if (empty($user))
			return $result;

		if ($user->banned)
			return $result;

		$user->banned    = true;
		$user->banned_at = date('Y-m-d H:i:s');
		$user->save();

		Activity::log(array(
			'contentId'   => $user->id,
			'contentType' => 'User',
			'action'      => 'Ban',
			'description' => 'Banned a User',
			'details'     => 'Username: '.$user->username,
		));

		$result['resultType'] = "Success";
		$result['message']    = Lang::get('fractal::messages.successBanned', array('item' => '<strong>'.$user->username.'</strong>'));
		return $result;
	}

	public function unban($id)
	{
		$result = array(
			'resultType' => 'Error',
			'message'    => Lang::get('fractal::messages.errorGeneral'),
		);

		$user = \User::find($id);
		if (empty($user))
			return $result;

		if (!$user->banned)
			return $result;

		$user->banned    = false;
		$user->banned_at = "0000-00-00 00:00:00";
		$user->save();

		Activity::log(array(
			'contentId'   => $user->id,
			'contentType' => 'User',
			'action'      => 'Unban',
			'description' => 'Unbanned a User',
			'details'     => 'Username: '.$user->username,
		));

		$result['resultType'] = "Success";
		$result['message']    = Lang::get('fractal::messages.successUnbanned', array('item' => '<strong>'.$user->username.'</strong>'));
		return $result;
	}

	public function destroy($id)
	{
		$result = array(
			'resultType' => 'Error',
			'message'    => Lang::get('fractal::messages.errorGeneral'),
		);

		$user = \User::find($id);
		if (empty($user))
			return $result;

		$user->delete();

		Activity::log(array(
			'contentId'   => $user->id,
			'contentType' => 'User',
			'action'      => 'Delete',
			'description' => 'Deleted a User',
			'details'     => 'Username: '.$user->username,
		));

		$result['resultType'] = "Success";
		$result['message']    = Lang::get('fractal::messages.successDeleted', array('item' => '<strong>'.$user->username.'</strong>'));
		return $result;
	}

}