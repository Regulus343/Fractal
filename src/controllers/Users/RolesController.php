<?php namespace Regulus\Fractal\Controllers\Users;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

use Fractal;

use Regulus\Fractal\Models\User\Role;

use Regulus\ActivityLog\Models\Activity;
use Form;
use Format;
use Site;

class RolesController extends UsersController {

	public function __construct()
	{
		parent::__construct();

		Fractal::setControllerPath($this);

		Site::set('section', 'Users');
		Site::set('subSection', 'Roles');
		Site::setTitle(Fractal::transChoice('labels.user_role', 2));

		Fractal::setContentType('user-role');

		Fractal::setViewsLocation('users.roles');

		Fractal::addTrailItem(Fractal::transChoice('labels.role', 2), Fractal::getControllerPath());

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
			'label' => Fractal::trans('labels.create_item', ['item' => Fractal::transChoice('labels.role')]),
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
		Site::setTitle(Fractal::trans('labels.create_item', ['item' => Fractal::transChoice('labels.role')]));
		Site::set('wysiwyg', true);

		Form::setErrors();

		Fractal::addButton([
			'label' => Fractal::trans('labels.return_to_items_list', ['items' => Fractal::transChoice('labels.role', 2)]),
			'icon'  => 'glyphicon glyphicon-list',
			'uri'   => Fractal::uri('', true),
		]);

		Fractal::addTrailItem(Fractal::trans('labels.create'), Request::url());

		return View::make(Fractal::view('form'));
	}

	public function store()
	{
		$tableName = Auth::getTableName('roles');

		$rules = [
			'role' => ['required', 'unique:'.$tableName],
			'name' => ['required', 'unique:'.$tableName],
		];
		Form::setValidationRules($rules);

		$messages = [];
		if (Form::validated())
		{
			$messages['success'] = Fractal::trans('messages.success.created', ['item' => Fractal::transChoice('labels.role')]);

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
			$messages['error'] = Fractal::trans('messages.errors.general');
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
				'error' => Fractal::trans('messages.errors.not_found', ['item' => Fractal::transChoiceLower('labels.role')])
			]);

		Site::setTitle($role->name.' ('.Fractal::transChoice('labels.role').')');
		Site::setHeading(Fractal::trans('labels.update_item', ['item' => Fractal::transChoice('labels.role')]).': <strong>'.Format::entities($role->name).'</strong>');
		Site::set('wysiwyg', true);

		Form::setDefaults($role);
		Form::setErrors();

		Fractal::addButton([
			'label' => Fractal::trans('labels.return_to_items_list', ['items' => Fractal::transChoice('labels.role', 2)]),
			'icon'  => 'glyphicon glyphicon-list',
			'uri'   => Fractal::uri('', true),
		]);

		Fractal::addTrailItem(Fractal::trans('labels.update'), Request::url());

		return View::make(Fractal::view('form'))->with('update', true);
	}

	public function update($id)
	{
		$role = Role::find($id);
		if (empty($role))
			return Redirect::to(Fractal::uri('', true))->with('messages', [
				'error' => Fractal::trans('messages.errors.not_found', ['item' => Fractal::transChoiceLower('labels.role')])
			]);

		$tableName = Auth::getTableName('roles');

		$rules = [
			'role' => ['required', 'unique:'.$tableName.',role,'.$id],
			'name' => ['required', 'unique:'.$tableName.',name,'.$id],
		];
		Form::setValidationRules($rules);
		Form::setErrors();

		$messages = [];
		if (Form::validated())
		{
			$messages['success'] = Fractal::trans('messages.success.updated', ['item' => Fractal::transChoice('labels.role')]);

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
			$messages['error'] = Fractal::trans('messages.errors.general');

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
			'message'    => Fractal::trans('messages.errors.general'),
		];

		$role = Role::find($id);
		if (empty($role))
			return $result;

		$existingUsers = $role->users()->count();
		if ($existingUsers)
		{
			$messageData = [
				'item'        => Fractal::trans('labels.role'),
				'total'       => $existingUsers,
				'relatedItem' => Format::pluralize(strtolower(Fractal::trans('labels.user')), $existingUsers),
			];

			$result['message'] = Fractal::trans('messages.error.delete_items_exist', $messageData);
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
		$result['message']    = Fractal::trans('messages.success.deleted', ['item' => '<strong>'.$role->name.'</strong>']);

		$role->rolePermissions()->sync([]);
		$role->delete();

		return $result;
	}

}