<?php namespace Regulus\Fractal\Controllers\Users;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

use Fractal;

use Regulus\Fractal\Models\Users\Role;

use Regulus\ActivityLog\Activity;
use \Form;
use \Format;
use \Site;

class RolesController extends UsersController {

	public function __construct()
	{
		parent::__construct();

		Fractal::setControllerPath($this);

		Site::set('section', 'Users');
		Site::set('subSection', 'Roles');
		Site::set('title', Fractal::lang('labels.userRoles'));

		Fractal::setContentType('user-role');

		Fractal::setViewsLocation('users.roles');

		Fractal::addTrailItem(Fractal::lang('labels.roles'), Fractal::getControllerPath());

		Site::set('defaultSorting', ['field' => 'display_order', 'order' => 'asc']);
	}

	public function index()
	{
		$data  = Fractal::setupPagination();
		$roles = Role::getSearchResults($data);

		Fractal::setContentForPagination($roles);

		$data     = Fractal::setPaginationMessage(true);
		$messages = Fractal::getPaginationMessageArray();

		if (!count($roles))
			$roles = Role::orderBy($data['sortField'], $data['sortOrder'])->paginate($data['itemsPerPage']);

		Fractal::addButton([
			'label' => Fractal::lang('labels.createRole'),
			'icon'  => 'glyphicon glyphicon-book',
			'uri'   => Fractal::uri('create', true),
		]);

		return View::make(Fractal::view('list'))
			->with('content', $roles)
			->with('messages', $messages);
	}

	public function search()
	{
		$data  = Fractal::setupPagination();
		$roles = Role::getSearchResults($data);

		Fractal::setContentForPagination($roles);

		$data = Fractal::setPaginationMessage();

		if (!count($roles))
			$data['content'] = User::orderBy($data['sortField'], $data['sortOrder'])->paginate($data['itemsPerPage']);

		$data['result']['pages']     = Fractal::getLastPage();
		$data['result']['tableBody'] = Fractal::createTable($data['content'], true);

		return $data['result'];
	}

	public function create()
	{
		Site::set('title', Fractal::lang('labels.createRole'));
		Site::set('wysiwyg', true);

		Form::setErrors();

		Fractal::addButton([
			'label' => Fractal::lang('labels.returnToRolesList'),
			'icon'  => 'glyphicon glyphicon-list',
			'uri'   => Fractal::uri('', true),
		]);

		Fractal::addTrailItem(Fractal::lang('labels.create'), Request::url());

		return View::make(Fractal::view('form'));
	}

	public function store()
	{
		Site::set('title', 'Create User Role');
		Site::set('wysiwyg', true);

		$tablePrefix = Config::get('identify::tablePrefix');
		$rules = [
			'role' => ['required', 'unique:'.$tablePrefix.'roles'],
			'name' => ['required', 'unique:'.$tablePrefix.'roles'],
		];
		Form::setValidationRules($rules);

		$messages = [];
		if (Form::validated()) {
			$messages['success'] = Fractal::lang('messages.successCreated', ['item' => Fractal::langLowerA('labels.role')]);

			$role = new Role;
			$role->role        = strtolower(trim(Input::get('role')));
			$role->name        = ucfirst(trim(Input::get('name')));
			$role->description = Input::get('description') != "" ? ucfirst(trim(Input::get('description'))) : null;
			$role->save();

			Activity::log([
				'contentId'   => $role->id,
				'contentType' => 'Role',
				'action'      => 'Create',
				'description' => 'Created a User Role',
				'details'     => 'Role: '.$role->name,
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

	public function edit($id)
	{
		$role = Role::find($id);
		if (empty($role))
			return Redirect::to(Fractal::uri('', true))->with('messages', [
				'error' => Fractal::lang('messages.errorNotFound', ['item' => Fractal::langLower('labels.role')])
			]);

		Site::set('title', $role->name.' ('.Fractal::lang('labels.role').')');
		Site::set('titleHeading', Fractal::lang('labels.updateRole').': <strong>'.Format::entities($role->name).'</strong>');
		Site::set('wysiwyg', true);

		Form::setDefaults($role);
		Form::setErrors();

		Fractal::addButton([
			'label' => Fractal::lang('labels.returnToRolesList'),
			'icon'  => 'glyphicon glyphicon-list',
			'uri'   => Fractal::uri('', true),
		]);

		Fractal::addTrailItem(Fractal::lang('labels.update'), Request::url());

		return View::make(Fractal::view('form'))->with('update', true);
	}

	public function update($id)
	{
		$role = Role::find($id);
		if (empty($role))
			return Redirect::to(Fractal::uri('', true))->with('messages', [
				'error' => Fractal::lang('messages.errorNotFound', ['item' => Fractal::langLower('labels.role')])
			]);

		Site::set('title', $role->name.' (User Role)');
		Site::set('titleHeading', 'Update User Role: <strong>'.Format::entities($role->name).'</strong>');
		Site::set('wysiwyg', true);

		$tablePrefix = Config::get('identify::tablePrefix');
		$rules = [
			'role' => ['required', 'unique:'.$tablePrefix.'roles,role,'.$id],
			'name' => ['required', 'unique:'.$tablePrefix.'roles,name,'.$id],
		];
		Form::setValidationRules($rules);
		Form::setErrors();

		$messages = [];
		if (Form::validated()) {
			$messages['success'] = Fractal::lang('messages.successUpdated', ['item' => Fractal::langLowerA('labels.role')]);

			$role->role        = strtolower(trim(Input::get('role')));
			$role->name        = ucfirst(trim(Input::get('name')));
			$role->description = Input::get('description') != "" ? ucfirst(trim(Input::get('description'))) : null;
			$role->save();

			Activity::log([
				'contentId'   => $role->id,
				'contentType' => 'Role',
				'action'      => 'Update',
				'description' => 'Updated a User Role',
				'details'     => 'Role: '.$role->name,
			]);

			return Redirect::to(Fractal::uri('', true))
				->with('messages', $messages);
		} else {
			$messages['error'] = Fractal::lang('messages.errorGeneral');

			return Redirect::to(Fractal::uri($id.'/edit', true))
				->with('messages', $messages)
				->with('errors', Form::getErrors())
				->withInput();
		}
	}

	public function destroy($id)
	{
		$result = [
			'resultType' => 'Error',
			'message'    => Fractal::lang('messages.errorGeneral'),
		];

		$role = Role::find($id);
		if (empty($role))
			return $result;

		$existingUsers = $role->users()->count();
		if ($existingUsers)
		{
			$messageData = [
				'item'        => Fractal::lang('labels.userRole'),
				'total'       => $existingUsers,
				'relatedItem' => Format::pluralize(strtolower(Fractal::lang('labels.user')), $existingUsers),
			];

			$result['message'] = Fractal::lang('messages.errorDeleteItemsExist', $messageData);
			return $result;
		}

		Activity::log([
			'contentId'   => $role->id,
			'contentType' => 'Role',
			'action'      => 'Delete',
			'description' => 'Deleted a User Role',
			'details'     => 'Role: '.$role->name,
		]);

		$result['resultType'] = "Success";
		$result['message']    = Fractal::lang('messages.successDeleted', ['item' => '<strong>'.$role->name.'</strong>']);

		$role->rolePermissions()->sync([]);
		$role->delete();

		return $result;
	}

}