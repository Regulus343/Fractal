<?php namespace Regulus\Fractal\Controllers\Users;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

use Fractal;

use Regulus\Fractal\Models\Role;

use Regulus\ActivityLog\Activity;
use \Form;
use \Format;
use \Site;

class RolesController extends BaseController {

	public function __construct()
	{
		parent::__construct();

		Site::set('section', 'Content');

		$subSection = "User Roles";
		Site::set('section', 'Users');
		Site::setMulti(array('subSection', 'title'), $subSection);

		Fractal::setContentType('user-role', true);

		Site::set('defaultSorting', array('field' => 'display_order', 'order' => 'asc'));

		Fractal::setViewsLocation('users.roles');

		Fractal::addTrailItem('Roles', 'users/roles');
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
			'label' => Lang::get('fractal::labels.createRole'),
			'icon'  => 'glyphicon glyphicon-star',
			'uri'   => 'users/roles/create',
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
		Site::set('title', 'Create User Role');
		Site::set('wysiwyg', true);

		Form::setErrors();

		Fractal::addButton([
			'label' => Lang::get('fractal::labels.returnToRolesList'),
			'icon'  => 'glyphicon glyphicon-list',
			'uri'   => 'users/roles',
		]);

		return View::make(Fractal::view('form'));
	}

	public function store()
	{
		Site::set('title', 'Create User Role');
		Site::set('wysiwyg', true);

		$tablePrefix = Config::get('identify::tablePrefix');
		$rules = array(
			'role' => array('required', 'unique:'.$tablePrefix.'roles'),
			'name' => array('required', 'unique:'.$tablePrefix.'roles'),
		);
		Form::setValidationRules($rules);

		$messages = array();
		if (Form::validated()) {
			$messages['success'] = Lang::get('fractal::messages.successCreated', array('item' => Format::a('page')));

			$role = new Role;
			$role->role = strtolower(trim(Input::get('role')));
			$role->name = ucfirst(trim(Input::get('name')));
			$role->save();

			Activity::log(array(
				'contentId'   => $role->id,
				'contentType' => 'UserRole',
				'action'      => 'Create',
				'description' => 'Created a User Role',
				'details'     => 'Role: '.$role->name,
			));

			return Redirect::to(Fractal::uri('user-roles'))
				->with('messages', $messages);
		} else {
			$messages['error'] = Lang::get('fractal::messages.errorGeneral');
		}

		return Redirect::to(Fractal::uri('user-roles/create'))
			->with('messages', $messages)
			->with('errors', Form::getErrors())
			->withInput();
	}

	public function edit($id)
	{
		$role = Role::find($id);
		if (empty($role))
			return Redirect::to(Fractal::uri('user-roles'))
				->with('messages', array('error' => Lang::get('fractal::messages.errorNotFound', array('item' => 'role'))));

		Site::set('title', $role->name.' (User Role)');
		Site::set('titleHeading', 'Update User Role: <strong>'.Format::entities($role->name).'</strong>');
		Site::set('wysiwyg', true);

		Form::setDefaults($role);
		Form::setErrors();

		Fractal::addButton([
			'label' => Lang::get('fractal::labels.returnToRolesList'),
			'icon'  => 'glyphicon glyphicon-list',
			'uri'   => 'users/roles',
		]);

		return View::make(Fractal::view('form'))->with('update', true);
	}

	public function update($id)
	{
		$role = Role::find($id);
		if (empty($role))
			return Redirect::to(Fractal::uri('user-roles'))
				->with('messages', array('error' => Lang::get('fractal::messages.errorNotFound', array('item' => 'role'))));

		Site::set('title', $role->name.' (User Role)');
		Site::set('titleHeading', 'Update User Role: <strong>'.Format::entities($role->name).'</strong>');
		Site::set('wysiwyg', true);

		$tablePrefix = Config::get('identify::tablePrefix');
		$rules = array(
			'role' => array('required', 'unique:'.$tablePrefix.'roles,role,'.$id),
			'name' => array('required', 'unique:'.$tablePrefix.'roles,name,'.$id),
		);
		Form::setValidationRules($rules);
		Form::setErrors();

		$messages = array();
		if (Form::validated()) {
			$messages['success'] = Lang::get('fractal::messages.successUpdated', array('item' => Format::a('page')));

			$role->role = strtolower(trim(Input::get('role')));
			$role->name = ucfirst(trim(Input::get('name')));
			$role->save();

			Activity::log(array(
				'contentId'   => $role->id,
				'contentType' => 'UserRole',
				'action'      => 'Update',
				'description' => 'Updated a User Role',
				'details'     => 'Role: '.$role->name,
			));

			return Redirect::to(Fractal::uri('user-roles'))
				->with('messages', $messages);
		} else {
			$messages['error'] = Lang::get('fractal::messages.errorGeneral');

			return Redirect::to(Fractal::uri('user-roles/'.$id.'/edit'))
				->with('messages', $messages)
				->with('errors', Form::getErrors())
				->withInput();
		}
	}

	public function destroy($id)
	{
		$result = array(
			'resultType' => 'Error',
			'message'    => Lang::get('fractal::messages.errorGeneral'),
		);

		$role = Role::find($id);
		if (empty($role))
			return $result;

		$existingUsers = $role->users()->count();
		if ($existingUsers) {
			$messageData = array(
				'item'        => Lang::get('fractal::labels.userRole'),
				'total'       => $existingUsers,
				'relatedItem' => Format::pluralize(strtolower(Lang::get('fractal::labels.user')), $existingUsers),
			);
			$result['message'] = Lang::get('fractal::messages.errorDeleteItemsExist', $messageData);
			return $result;
		}

		Activity::log(array(
			'contentId'   => $role->id,
			'contentType' => 'Role',
			'action'      => 'Delete',
			'description' => 'Deleted a Role',
			'details'     => 'Role: '.$role->name,
		));

		$result['resultType'] = "Success";
		$result['message']    = Lang::get('fractal::messages.successDeleted', array('item' => '<strong>'.$role->name.'</strong>'));

		$role->delete();

		return $result;
	}

}