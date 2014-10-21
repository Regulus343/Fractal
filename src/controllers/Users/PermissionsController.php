<?php namespace Regulus\Fractal\Controllers\Users;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

use Fractal;

use Regulus\Fractal\Models\Permission;

use Regulus\ActivityLog\Activity;
use \Form;
use \Format;
use \Site;

class PermissionsController extends UsersController {

	public function __construct()
	{
		parent::__construct();

		Fractal::setControllerPath($this);

		Site::set('section', 'Users');
		Site::set('subSection', 'Permissions');
		Site::set('title', Fractal::lang('labels.userPermissions'));

		Fractal::setContentType('user-permission', true);

		Site::set('defaultSorting', ['field' => 'display_order', 'order' => 'asc']);

		Fractal::setViewsLocation('users.permissions');

		Fractal::addTrailItem('Permissions', Fractal::getControllerPath());
	}

	public function index()
	{
		$data        = Fractal::setupPagination();
		$permissions = Permission::getSearchResults($data);

		Fractal::setContentForPagination($permissions);

		$data     = Fractal::setPaginationMessage(true);
		$messages = Fractal::getPaginationMessageArray();

		if (!count($permissions))
			$permissions = Permission::orderBy($data['sortField'], $data['sortOrder'])->paginate($data['itemsPerPage']);

		Fractal::addButton([
			'label' => Fractal::lang('labels.createPermission'),
			'icon'  => 'glyphicon glyphicon-star',
			'uri'   => Fractal::uri('create', true),
		]);

		return View::make(Fractal::view('list'))
			->with('content', $permissions)
			->with('messages', $messages);
	}

	public function search()
	{
		$data        = Fractal::setupPagination();
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
		Site::set('title', 'Create User Permission');
		Site::set('wysiwyg', true);

		Form::setErrors();

		Fractal::addButton([
			'label' => Fractal::lang('labels.returnToPermissionsList'),
			'icon'  => 'glyphicon glyphicon-list',
			'uri'   => Fractal::uri('', true),
		]);

		return View::make(Fractal::view('form'));
	}

	public function store()
	{
		Site::set('title', 'Create User Permission');
		Site::set('wysiwyg', true);

		$tablePrefix = Config::get('identify::tablePrefix');
		$rules = [
			'permission' => ['required', 'unique:'.$tablePrefix.'permissions'],
			'name'       => ['required', 'unique:'.$tablePrefix.'permissions'],
		];
		Form::setValidationRules($rules);

		$messages = [];
		if (Form::validated()) {
			$messages['success'] = Fractal::lang('messages.successCreated', ['item' => Fractal::langLowerA('labels.permission')]);

			$permission = new Permission;
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
		$permission = Role::find($id);
		if (empty($permission))
			return Redirect::to(Fractal::uri('', true))->with('messages', [
				'error' => Fractal::lang('messages.errorNotFound', ['item' => Fractal::langLower('labels.permission')])
			]);

		Site::set('title', $permission->name.' (User Role)');
		Site::set('titleHeading', 'Update User Role: <strong>'.Format::entities($permission->name).'</strong>');
		Site::set('wysiwyg', true);

		Form::setDefaults($permission);
		Form::setErrors();

		Fractal::addButton([
			'label' => Fractal::lang('labels.returnToPermissionsList'),
			'icon'  => 'glyphicon glyphicon-list',
			'uri'   => Fractal::uri('', true),
		]);

		return View::make(Fractal::view('form'))->with('update', true);
	}

	public function update($id)
	{
		$permission = Role::find($id);
		if (empty($permission))
			return Redirect::to(Fractal::uri('', true))->with('messages', [
				'error' => Fractal::lang('messages.errorNotFound', ['item' => Fractal::langLower('labels.permission')])
			]);

		Site::set('title', $permission->name.' (User Role)');
		Site::set('titleHeading', 'Update User Role: <strong>'.Format::entities($permission->name).'</strong>');
		Site::set('wysiwyg', true);

		$tablePrefix = Config::get('identify::tablePrefix');
		$rules = [
			'permission' => ['required', 'unique:'.$tablePrefix.'permissions,permission,'.$id],
			'name'       => ['required', 'unique:'.$tablePrefix.'permissions,name,'.$id],
		];
		Form::setValidationRules($rules);
		Form::setErrors();

		$messages = [];
		if (Form::validated()) {
			$messages['success'] = Fractal::lang('messages.successUpdated', ['item' => Fractal::langLowerA('labels.permission')]);

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

		$permission = Permission::find($id);

		Activity::log([
			'contentId'   => $permission->id,
			'contentType' => 'Permission',
			'action'      => 'Delete',
			'description' => 'Deleted a User Permission',
			'details'     => 'Permission: '.$permission->name,
		]);

		$result['resultType'] = "Success";
		$result['message']    = Fractal::lang('messages.successDeleted', ['item' => '<strong>'.$permission->name.'</strong>']);

		$permission->users()->sync([]);
		$permission->roles()->sync([]);
		$permission->delete();

		return $result;
	}

}