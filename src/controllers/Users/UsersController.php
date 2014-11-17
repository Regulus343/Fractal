<?php namespace Regulus\Fractal\Controllers\Users;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

use Fractal;

use Regulus\Fractal\Models\Users\User;
use Regulus\Fractal\Models\Users\Role;

use Regulus\ActivityLog\Activity;
use \Form;
use \Format;
use \Site;

class UsersController extends BaseController {

	public function __construct()
	{
		parent::__construct();

		Fractal::setControllerPath(get_class());

		Site::setMulti(['section', 'subSection'], 'Users');
		Site::set('title', Fractal::lang('labels.users'));

		//set content type and views location
		Fractal::setContentType('user', true);

		Fractal::addTrailItem(Fractal::lang('labels.users'), Fractal::getControllerPath());
	}

	public function index()
	{
		$data  = Fractal::setupPagination();
		$users = User::getSearchResults($data);

		Fractal::setContentForPagination($users);

		$data     = Fractal::setPaginationMessage(true);
		$messages = Fractal::getPaginationMessageArray();

		if (!count($users))
			$users = User::orderBy($data['sortField'], $data['sortOrder'])->paginate($data['itemsPerPage']);

		Fractal::addButton([
			'label' => Fractal::lang('labels.createUser'),
			'icon'  => 'glyphicon glyphicon-user',
			'uri'   => Fractal::uri('create', true),
		]);

		return View::make(Fractal::view('list'))
			->with('content', $users)
			->with('messages', $messages);
	}

	public function search()
	{
		$data  = Fractal::setupPagination('Users');
		$users = User::getSearchResults($data);

		Fractal::setContentForPagination($users);

		$data = Fractal::setPaginationMessage();

		if (!count($users))
			$data['content'] = User::orderBy($data['sortField'], $data['sortOrder'])->paginate($data['itemsPerPage']);

		$data['result']['pages']     = Fractal::getLastPage();
		$data['result']['tableBody'] = Fractal::createTable($data['content'], true);

		return $data['result'];
	}

	public function create()
	{
		Site::set('title', Fractal::lang('labels.createUser'));
		Site::set('wysiwyg', true);

		$defaults    = ['active' => true];
		$defaultRole = Role::where('default', true)->first();

		if (!empty($defaultRole))
			$defaults['roles.'.$defaultRole->id] = $defaultRole->id;

		Form::setDefaults($defaults);
		Form::setErrors();

		Fractal::addButton([
			'label' => Fractal::lang('labels.returnToUsersList'),
			'icon'  => 'glyphicon glyphicon-list',
			'uri'   => Fractal::uri('', true),
		]);

		Fractal::addTrailItem(Fractal::lang('labels.create'), Request::url());

		return View::make(Fractal::view('form'));
	}

	public function store()
	{
		Site::set('title', 'Create User');
		Site::set('wysiwyg', true);

		$rules = [
			'username' => ['required', 'alpha_dash', 'min:3', 'unique:auth_users,username'],
			'email'    => ['required', 'email'],
			'roles'    => ['required'],
			'password' => ['required', 'confirmed'],
		];

		if (Fractal::getSetting('Require Unique Email Addresses'))
			$rules['email'][] = 'unique:auth_users,email';

		$minPasswordLength = Fractal::getSetting('Minimum Password Length');
		if ($minPasswordLength)
			$rules['password'][] = 'min:'.$minPasswordLength;

		Form::setValidationRules($rules);

		$messages = [];
		if (Form::validated()) {
			$messages['success'] = Fractal::lang('messages.successCreated', ['item' => Fractal::langLowerA('labels.user')]);

			$user = new User;
			$user->fill(Input::except('csrf_token', 'roles', 'password', 'password_confirmation'));
			$user->first_name = Format::name($user->first_name);
			$user->last_name  = Format::name($user->last_name);
			$user->password   = Hash::make(Input::get('password'));
			$user->save();

			$user->roles()->sync(Input::get('roles'));

			Activity::log([
				'contentId'   => $user->id,
				'contentType' => 'User',
				'action'      => 'Create',
				'description' => 'Created a User',
				'details'     => 'Username: '.$user->username,
			]);

			return Redirect::to(Fractal::uri('', true))
				->with('messages', $messages);
		} else {
			$messages['error'] = Fractal::lang('messages.errorGeneral');
		}

		return Redirect::to(Fractal::uri('create', true))
			->with('messages', $messages)
			->with('errors', Form::getErrors())
			->withInput();
	}

	public function edit($username)
	{
		$user = Fractal::userByUsername($username);
		if (empty($user))
			return Redirect::to(Fractal::uri('', true))->with('messages', [
				'error' => Fractal::lang('messages.errorNotFound', ['item' => Fractal::langLower('labels.user')])
			]);

		Site::set('title', $user->username.' ('.Fractal::lang('labels.user').')');
		Site::set('titleHeading', Fractal::lang('labels.updateUser').': <strong>'.Format::entities($user->username).'</strong>');
		Site::set('wysiwyg', true);

		Form::setDefaults($user, ['roles' => 'id']);
		Form::setErrors();

		Fractal::addButton([
			'label' => Fractal::lang('labels.returnToUsersList'),
			'icon'  => 'glyphicon glyphicon-list',
			'uri'   => Fractal::uri('', true),
		]);

		Fractal::addTrailItem(Fractal::lang('labels.update'), Request::url());

		return View::make(Fractal::view('form'))->with('update', true);
	}

	public function update($username)
	{
		$user = Fractal::userByUsername($username);
		if (empty($user))
			return Redirect::to(Fractal::uri('', true))->with('messages', [
				'error' => Fractal::lang('messages.errorNotFound', ['item' => Fractal::langLower('labels.user')])
			]);

		Site::set('title', $user->username.' (User)');
		Site::set('titleHeading', 'Update User: <strong>'.Format::entities($user->username).'</strong>');
		Site::set('wysiwyg', true);

		$rules = [
			'username' => ['required', 'alpha_dash', 'min:3', 'unique:auth_users,username,'.$user->id],
			'email'    => ['required', 'email'],
			'roles'    => ['required'],
		];

		if (Fractal::getSetting('Require Unique Email Addresses'))
			$rules['email'][] = 'unique:auth_users,email,'.$user->id;

		$minPasswordLength = Fractal::getSetting('Minimum Password Length');
		if ($minPasswordLength)
			$rules['password'][] = 'min:'.$minPasswordLength;

		Form::setValidationRules($rules);

		$messages = [];
		if (Form::validated()) {
			$messages['success'] = Fractal::lang('messages.successUpdated', ['item' => Fractal::langLowerA('labels.user')]);

			$user->fill(Input::except('csrf_token', 'roles'));
			$user->first_name = Format::name($user->first_name);
			$user->last_name  = Format::name($user->last_name);
			$user->save();

			$user->roles()->sync(Input::get('roles'));

			Activity::log([
				'contentId'   => $user->id,
				'contentType' => 'User',
				'action'      => 'Update',
				'description' => 'Updated a User',
				'details'     => 'Username: '.$user->username,
			]);

			return Redirect::to(Fractal::uri('', true))
				->with('messages', $messages);
		} else {
			$messages['error'] = Fractal::lang('messages.errorGeneral');

			return Redirect::to(Fractal::uri($username.'/edit', true))
				->with('messages', $messages)
				->with('errors', Form::getErrors())
				->withInput();
		}
	}

	public function ban($id)
	{
		$result = [
			'resultType' => 'Error',
			'message'    => Fractal::lang('messages.errorGeneral'),
		];

		$user = User::find($id);
		if (empty($user))
			return $result;

		if ($user->isBanned())
			return $result;

		$user->banned_at = date('Y-m-d H:i:s');
		$user->save();

		Activity::log([
			'contentId'   => $user->id,
			'contentType' => 'User',
			'action'      => 'Ban',
			'description' => 'Banned a User',
			'details'     => 'Username: '.$user->username,
		]);

		$result['resultType'] = "Success";
		$result['message']    = Fractal::lang('messages.successBanned', ['item' => '<strong>'.$user->username.'</strong>']);
		return $result;
	}

	public function unban($id)
	{
		$result = [
			'resultType' => 'Error',
			'message'    => Fractal::lang('messages.errorGeneral'),
		];

		$user = User::find($id);
		if (empty($user))
			return $result;

		if (!$user->isBanned())
			return $result;

		$user->banned_at = null;
		$user->save();

		Activity::log([
			'contentId'   => $user->id,
			'contentType' => 'User',
			'action'      => 'Unban',
			'description' => 'Unbanned a User',
			'details'     => 'Username: '.$user->username,
		]);

		$result['resultType'] = "Success";
		$result['message']    = Fractal::lang('messages.successUnbanned', ['item' => '<strong>'.$user->username.'</strong>']);
		return $result;
	}

	public function destroy($id)
	{
		$result = [
			'resultType' => 'Error',
			'message'    => Fractal::lang('messages.errorGeneral'),
		];

		$user = \User::find($id);
		if (empty($user))
			return $result;

		$user->roles()->sync([]);
		$user->userPermissions()->sync([]);
		$user->delete();

		Activity::log([
			'contentId'   => $user->id,
			'contentType' => 'User',
			'action'      => 'Delete',
			'description' => 'Deleted a User',
			'details'     => 'Username: '.$user->username,
		]);

		$result['resultType'] = "Success";
		$result['message']    = Fractal::lang('messages.successDeleted', ['item' => '<strong>'.$user->username.'</strong>']);
		return $result;
	}

}