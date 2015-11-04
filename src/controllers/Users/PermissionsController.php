<?php namespace Regulus\Fractal\Controllers\Users;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

use Fractal;

use Regulus\Fractal\Models\User\Permission;

use Regulus\ActivityLog\Models\Activity;
use Auth;
use Form;
use Format;
use Site;

class PermissionsController extends UsersController {

	public function __construct()
	{
		parent::__construct();

		Fractal::setControllerPath($this);

		Site::set('section', 'Users');
		Site::set('subSection', 'Permissions');
		Site::setTitle(Fractal::transChoice('labels.user_permission', 2));

		Fractal::setContentType('user-permission');

		Fractal::setViewsLocation('users.permissions');

		Fractal::addTrailItem(Fractal::transChoice('labels.permission', 2), Fractal::getControllerPath());

		Site::set('defaultSorting', ['field' => 'display_order', 'order' => 'asc']);
	}

	public function index()
	{
		$data        = Fractal::initPagination();
		$permissions = Permission::getSearchResults($data);

		Fractal::setContentForPagination($permissions);

		$data     = Fractal::setPaginationMessage(true);
		$messages = Fractal::getPaginationMessageArray();

		if (!count($permissions))
			$permissions = Permission::orderBy($data['sortField'], $data['sortOrder'])->paginate($data['itemsPerPage']);

		Fractal::addButton([
			'label' => Fractal::trans('labels.create_item', ['item' => Fractal::transChoice('labels.permission')]),
			'icon'  => 'star',
			'uri'   => Fractal::uri('create', true),
		]);

		$rootPermissions = Permission::whereNull('parent_id')->orderBy('display_order')->get();

		return View::make(Fractal::view('list'))
			->with('content', $permissions)
			->with('rootPermissions', $rootPermissions)
			->with('messages', $messages);
	}

	public function search()
	{
		$data        = Fractal::initPagination();
		$permissions = Permission::getSearchResults($data);

		Fractal::setContentForPagination($permissions);

		$data = Fractal::setPaginationMessage();

		if (!count($permissions))
			$data['content'] = User::orderBy($data['sortField'], $data['sortOrder'])->paginate($data['itemsPerPage']);

		$data['result']['pages']     = Fractal::getLastPage();
		$data['result']['tableBody'] = Fractal::createTable($data['content'], true);

		return $data['result'];
	}

	public function create()
	{
		Site::setTitle(Fractal::trans('labels.create_item', ['item' => Fractal::transChoice('labels.permission')]));
		Site::set('wysiwyg', true);

		Form::setErrors();

		Fractal::addButton([
			'label' => Fractal::trans('labels.return_to_items_list', ['items' => Fractal::transChoice('labels.permission', 2)]),
			'icon'  => 'list',
			'uri'   => Fractal::uri('', true),
		]);

		return View::make(Fractal::view('form'));
	}

	public function store()
	{
		$tableName = Auth::getTableName('permissions');

		$rules = [
			'permission' => ['required', 'unique:'.$tableName],
			'name'       => ['required', 'unique:'.$tableName],
		];
		Form::setValidationRules($rules);

		$messages = [];
		if (Form::isValid())
		{
			$messages['success'] = Fractal::trans('messages.success.created', ['item' => Fractal::transChoice('labels.permission')]);

			$permission = new Permission;

			$permission->parent_id   = Input::get('parent_id');
			$permission->permission  = strtolower(trim(Input::get('permission')));
			$permission->name        = ucfirst(trim(Input::get('name')));
			$permission->description = Input::get('description') != "" ? ucfirst(trim(Input::get('description'))) : null;
			$permission->save();

			Activity::log([
				'contentId'   => $permission->id,
				'contentType' => 'Permission',
				'action'      => 'Create',
				'description' => 'Created a User Permission',
				'details'     => 'Permission: '.$permission->name,
			]);

			Fractal::addTrailItem(Fractal::trans('labels.create'), Request::url());

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
		$permission = Permission::find($id);
		if (empty($permission))
			return Redirect::to(Fractal::uri('', true))->with('messages', [
				'error' => Fractal::trans('messages.errors.not_found', ['item' => Fractal::transChoiceLower('labels.permission')])
			]);

		Site::setTitle($permission->name.' ('.Fractal::transChoice('labels.permission').')');
		Site::setHeading(Fractal::trans('labels.update_item', ['item' => Fractal::transChoice('labels.permission')]).': <strong>'.Format::entities($permission->name).'</strong>');
		Site::set('wysiwyg', true);

		Form::setDefaults($permission);
		Form::setErrors();

		Fractal::addButton([
			'label' => Fractal::trans('labels.return_to_items_list', ['items' => Fractal::transChoice('labels.permission', 2)]),
			'icon'  => 'list',
			'uri'   => Fractal::uri('', true),
		]);

		Fractal::addTrailItem(Fractal::trans('labels.update'), Request::url());

		return View::make(Fractal::view('form'))->with('update', true);
	}

	public function update($id)
	{
		$permission = Permission::find($id);
		if (empty($permission))
			return Redirect::to(Fractal::uri('', true))->with('messages', [
				'error' => Fractal::trans('messages.errors.not_found', ['item' => Fractal::transChoiceLower('labels.permission')])
			]);

		$tableName = Auth::getTableName('permissions');

		$rules = [
			'permission' => ['required', 'unique:'.$tableName.',permission,'.$id],
			'name'       => ['required', 'unique:'.$tableName.',name,'.$id],
		];
		Form::setValidationRules($rules);
		Form::setErrors();

		$messages = [];
		if (Form::isValid())
		{
			$messages['success'] = Fractal::trans('messages.success.updated', ['item' => Fractal::transChoice('labels.permission')]);

			$permission->parent_id   = Input::get('parent_id');
			$permission->permission  = strtolower(trim(Input::get('permission')));
			$permission->name        = ucfirst(trim(Input::get('name')));
			$permission->description = Input::get('description') != "" ? ucfirst(trim(Input::get('description'))) : null;
			$permission->save();

			Activity::log([
				'contentId'   => $permission->id,
				'contentType' => 'Permission',
				'action'      => 'Update',
				'description' => 'Updated a User Permission',
				'details'     => 'Permission: '.$permission->name,
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

		$permission = Permission::find($id);

		if ($permission->subPermissions()->count())
			return $result;

		Activity::log([
			'contentId'   => $permission->id,
			'contentType' => 'Permission',
			'action'      => 'Delete',
			'description' => 'Deleted a User Permission',
			'details'     => 'Permission: '.$permission->name,
		]);

		$result['resultType'] = "Success";
		$result['message']    = Fractal::trans('messages.success.deleted', ['item' => '<strong>'.$permission->name.'</strong>']);

		$permission->users()->sync([]);
		$permission->roles()->sync([]);
		$permission->delete();

		return $result;
	}

}