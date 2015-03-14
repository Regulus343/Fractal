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
		Site::setTitle(Fractal::trans('labels.users'));

		// set content type and views location
		Fractal::setContentType('user', true);

		Fractal::addTrailItem(Fractal::trans('labels.users'), Fractal::getControllerPath());
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
			'label' => Fractal::trans('labels.createUser'),
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
		Site::setTitle(Fractal::trans('labels.create_item', ['item' => Fractal::transChoice('labels.user')]));
		Site::set('wysiwyg', true);

		$defaults    = ['active' => true];
		$defaultRole = Role::where('default', true)->first();

		if (!empty($defaultRole))
			$defaults['roles.'.$defaultRole->id] = $defaultRole->id;

		Form::setDefaults($defaults);
		Form::setErrors();

		Fractal::addButton([
			'label' => Fractal::trans('labels.return_to_items_list', ['item' => Fractal::transChoice('labels.user')]),
			'icon'  => 'glyphicon glyphicon-list',
			'uri'   => Fractal::uri('', true),
		]);

		Fractal::addTrailItem(Fractal::trans('labels.create'), Request::url());

		return View::make(Fractal::view('form'));
	}

	public function store()
	{
		$rules = [
			'username' => ['required', 'alpha_dash', 'min:2', 'unique:auth_users,username'],
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
		if (Form::validated())
		{
			$messages['success'] = Fractal::trans('messages.successCreated', ['item' => Fractal::transLowerA('labels.user')]);

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
			$messages['error'] = Fractal::trans('messages.errorGeneral');
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
				'error' => Fractal::trans('messages.errorNotFound', ['item' => Fractal::transLower('labels.user')])
			]);

		Site::setTitle($user->username.' ('.Fractal::trans('labels.user').')');
		Site::setHeading(Fractal::trans('labels.update_item', ['item' => Fractal::transChoice('labels.user')]).': <strong>'.Format::entities($user->username).'</strong>');
		Site::set('wysiwyg', true);

		Form::setDefaults($user, ['roles' => 'id']);
		Form::setErrors();

		Fractal::addButton([
			'label' => Fractal::trans('labels.returnToUsersList'),
			'icon'  => 'glyphicon glyphicon-list',
			'uri'   => Fractal::uri('', true),
		]);

		Fractal::addTrailItem(Fractal::trans('labels.update'), Request::url());

		return View::make(Fractal::view('form'))->with('update', true);
	}

	public function update($username)
	{
		$user = Fractal::userByUsername($username);
		if (empty($user))
			return Redirect::to(Fractal::uri('', true))->with('messages', [
				'error' => Fractal::trans('messages.errorNotFound', ['item' => Fractal::transLower('labels.user')])
			]);

		$rules = [
			'username' => ['required', 'alpha_dash', 'min:2', 'unique:auth_users,username,'.$user->id],
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
		if (Form::validated())
		{
			$messages['success'] = Fractal::trans('messages.success.updated', ['item' => Fractal::transChoice('labels.user')]);

			$user->fill(Input::except('roles'));

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
			$messages['error'] = Fractal::trans('messages.errors.general');

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
			'message'    => Fractal::trans('messages.errors.general'),
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
		$result['message']    = Fractal::trans('messages.success.banned', ['item' => '<strong>'.$user->username.'</strong>']);
		return $result;
	}

	public function unban($id)
	{
		$result = [
			'resultType' => 'Error',
			'message'    => Fractal::trans('messages.errors.general'),
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
		$result['message']    = Fractal::trans('messages.success.unbanned', ['item' => '<strong>'.$user->username.'</strong>']);
		return $result;
	}

	public function destroy($id)
	{
		$result = [
			'resultType' => 'Error',
			'message'    => Fractal::trans('messages.errors.general'),
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
		$result['message']    = Fractal::trans('messages.success.deleted', ['item' => '<strong>'.$user->username.'</strong>']);
		return $result;
	}

}