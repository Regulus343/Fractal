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

use Regulus\Fractal\Models\User\User;
use Regulus\Fractal\Models\User\Role;
use Regulus\Fractal\Models\User\Permission;

use Regulus\ActivityLog\Models\Activity;
use Form;
use Format;
use Site;

class UsersController extends BaseController {

	public function __construct()
	{
		parent::__construct();

		Fractal::setControllerPath(get_class());

		Site::setMulti(['section', 'subSection'], 'Users');
		Site::setTitle(Fractal::transChoice('labels.user', 2));

		// set content type and views location
		Fractal::setContentType('user', true);

		Fractal::addTrailItem(Fractal::transChoice('labels.user', 2), Fractal::getControllerPath());
	}

	public function index()
	{
		$data  = Fractal::initPagination();
		$users = User::getSearchResults($data);

		Fractal::setContentForPagination($users);

		$data     = Fractal::setPaginationMessage(true);
		$messages = Fractal::getPaginationMessageArray();

		if (!count($users))
			$users = User::orderBy($data['sortField'], $data['sortOrder'])->paginate($data['itemsPerPage']);

		Fractal::addButton([
			'label' => Fractal::trans('labels.create_item', ['item' => Fractal::transChoice('labels.user')]),
			'icon'  => 'user',
			'uri'   => Fractal::uri('create', true),
		]);

		return View::make(Fractal::view('list'))
			->with('content', $users)
			->with('messages', $messages);
	}

	public function search()
	{
		$data  = Fractal::initPagination();
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
			'icon'  => 'list',
			'uri'   => Fractal::uri('', true),
		]);

		Fractal::addTrailItem(Fractal::trans('labels.create'), Request::url());

		$permissions = Permission::whereNull('parent_id')->orderBy('display_order')->get();

		return View::make(Fractal::view('form'))->with('permissions', $permissions);
	}

	public function store()
	{
		$rules = [
			'name'     => ['required', 'alpha_dash', 'min:2', 'unique:auth_users,name'],
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
		if (Form::isValid())
		{
			$messages['success'] = Fractal::trans('messages.success.created', ['item' => Fractal::transLowerA('labels.user')]);

			$user = new User;

			$user->fill(Input::except('_token', 'roles', 'password', 'password_confirmation'));

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
				'details'     => 'Username: '.$user->name,
			]);

			return Redirect::to(Fractal::uri($user->name.'/edit', true))
				->with('messages', $messages);
		} else {
			$messages['error'] = Fractal::trans('messages.errors.general');
		}

		return Redirect::to(Fractal::uri('create', true))
			->with('messages', $messages)
			->with('errors', Form::getErrors())
			->withInput();
	}

	public function edit($name)
	{
		$user = User::where('name', $name)->first();
		if (empty($user))
		{
			// attempt to find by ID and redirect to slugged URL if found
			$user = User::find($name);

			if (!empty($user))
				return Redirect::to(Fractal::uri($user->name.'/edit', true));
			else
				return Redirect::to(Fractal::uri('', true))->with('messages', [
					'error' => Fractal::trans('messages.errors.not_found', ['item' => Fractal::transChoiceLower('labels.user')])
				]);
		}

		Site::setTitle($user->name.' ('.Fractal::transChoice('labels.user').')');
		Site::setHeading(Fractal::trans('labels.update_item', ['item' => Fractal::transChoice('labels.user')]).': <strong>'.Format::entities($user->name).'</strong>');
		Site::set('wysiwyg', true);

		Form::setDefaults($user, ['roles' => 'id']);
		Form::setErrors();

		Fractal::addButton([
			'label' => Fractal::trans('labels.return_to_items_list', ['item' => Fractal::transChoice('labels.user')]),
			'icon'  => 'list',
			'uri'   => Fractal::uri('', true),
		]);

		Fractal::addTrailItem(Fractal::trans('labels.update'), Request::url());

		$permissions = Permission::whereNull('parent_id')->orderBy('display_order')->get();

		return View::make(Fractal::view('form'))
			->with('update', true)
			->with('form', 'User')
			->with('user', $user)
			->with('permissions', $permissions);
	}

	public function update($name)
	{
		$user = User::where('name', $name)->first();
		if (empty($user))
			return Redirect::to(Fractal::uri('', true))->with('messages', [
				'error' => Fractal::trans('messages.errors.not_found', ['item' => Fractal::transChoiceLower('labels.user')])
			]);

		$rules = [
			'name'  => ['required', 'alpha_dash', 'min:2', 'unique:auth_users,name,'.$user->id],
			'email' => ['required', 'email'],
			'roles' => ['required'],
		];

		if (Fractal::getSetting('Require Unique Email Addresses'))
			$rules['email'][] = 'unique:auth_users,email,'.$user->id;

		$minPasswordLength = Fractal::getSetting('Minimum Password Length');
		if ($minPasswordLength)
			$rules['password'][] = 'min:'.$minPasswordLength;

		Form::setValidationRules($rules);

		$messages = [];
		if (Form::isValid())
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
				'details'     => 'Username: '.$user->name,
			]);

			return Redirect::to(Fractal::uri('', true))
				->with('messages', $messages);
		} else {
			$messages['error'] = Fractal::trans('messages.errors.general');

			return Redirect::to(Fractal::uri($name.'/edit', true))
				->with('messages', $messages)
				->with('errors', Form::getErrors())
				->withInput();
		}
	}

	public function ban($id)
	{
		$user = User::find($id);
		if (empty($user))
			return $this->error();

		if ($user->isBanned())
			return $this->error();

		$user->banned_at = date('Y-m-d H:i:s');
		$user->save();

		Activity::log([
			'contentId'   => $user->id,
			'contentType' => 'User',
			'action'      => 'Ban',
			'description' => 'Banned a User',
			'details'     => 'Username: '.$user->name,
		]);

		return $this->success(Fractal::trans('messages.success.banned', ['item' => '<strong>'.$user->name.'</strong>']));
	}

	public function unban($id)
	{
		$user = User::find($id);
		if (empty($user))
			return $this->error();

		if (!$user->isBanned())
			return $this->error();

		$user->banned_at = null;
		$user->save();

		Activity::log([
			'contentId'   => $user->id,
			'contentType' => 'User',
			'action'      => 'Unban',
			'description' => 'Unbanned a User',
			'details'     => 'Username: '.$user->name,
		]);

		return $this->success(Fractal::trans('messages.success.unbanned', ['item' => '<strong>'.$user->name.'</strong>']));
	}

	public function destroy($id)
	{
		$user = User::find($id);
		if (empty($user))
			return $this->error();

		$user->roles()->sync([]);
		$user->userPermissions()->sync([]);
		$user->delete();

		Activity::log([
			'contentId'   => $user->id,
			'contentType' => 'User',
			'action'      => 'Delete',
			'description' => 'Deleted a User',
			'details'     => 'Username: '.$user->name,
		]);

		return $this->success(Fractal::trans('messages.success.deleted', ['item' => '<strong>'.$user->name.'</strong>']));
	}

}