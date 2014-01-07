<?php namespace Regulus\Fractal;

use \BaseController;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL;

use Aquanode\Formation\Formation as Form;
use Regulus\ActivityLog\Activity;
use Regulus\SolidSite\SolidSite as Site;
use Regulus\TetraText\TetraText as Format;

use Regulus\Identify\Role as Role;

class UserRolesController extends BaseController {

	public function __construct()
	{
		Site::set('section', 'Content');

		$subSection = "User Roles";
		Site::set('section', 'Users');
		Site::setMulti(array('subSection', 'title'), $subSection);

		Fractal::setContentType('roles', true);

		Site::set('defaultSorting', array('field' => 'display_order', 'order' => 'desc'));

		Fractal::setViewsLocation('users.activity');
	}

	public function index()
	{
		$data = Fractal::setupPagination();

		$roles = Role::orderBy($data['sortField'], $data['sortOrder']);
		if ($data['sortField'] != "id") $roles->orderBy('id', 'asc');
		if ($data['terms'] != "") {
			$roles->where(function($query) use ($data) {
				$query
					->where('role', 'like', $data['likeTerms'])
					->orWhere('name', 'like', $data['likeTerms']);
			});
		}
		$roles = $roles->paginate($data['itemsPerPage']);

		Fractal::setContentForPagination($roles);

		$data = Fractal::setPaginationMessage();
		$messages['success'] = $data['result']['message'];

		$defaults = array(
			'search' => $data['terms']
		);
		Form::setDefaults($defaults);

		return View::make(Fractal::view('list'))
			->with('content', $roles)
			->with('messages', $messages);
	}

	public function search()
	{
		$data = Fractal::setupPagination();

		$roles = Role::orderBy($data['sortField'], $data['sortOrder']);
		if ($data['sortField'] != "id") $roles->orderBy('id', 'asc');
		if ($data['terms'] != "") {
			$roles->where(function($query) use ($data) {
				$query
					->where('role', 'like', $data['likeTerms'])
					->orWhere('name', 'like', $data['likeTerms']);
			});
		}
		$roles = $roles->paginate($data['itemsPerPage']);

		Fractal::setContentForPagination($roles);

		if (count($roles)) {
			$data = Fractal::setPaginationMessage();
		} else {
			$data['content'] = User::orderBy('id')->paginate($data['itemsPerPage']);
			if ($data['terms'] == "") $data['result']['message'] = Lang::get('fractal::messages.searchNoTerms');
		}

		$data['result']['pages']     = Fractal::getLastPage();
		$data['result']['tableBody'] = Fractal::createTable($data['content'], true);

		return $data['result'];
	}

	public function create()
	{
		Site::set('title', 'Create User Role');
		Site::set('wysiwyg', true);
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

			return Redirect::to(Fractal::uri('user-roles'))
				->with('messages', $messages);
		} else {
			$messages['error'] = Lang::get('fractal::messages.errorGeneral');
		}

		return View::make(Fractal::view('form'))
			->with('messages', $messages);
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
				'item'        => Lang::get('fractal::labels.role'),
				'total'       => $existingUsers,
				'relatedItem' => Format::pluralize(strtolower(Lang::get('fractal::labels.user')), $existingUsers),
			);
			$result['message'] = Lang::get('fractal::messages.errorDeleteItemsExist', $messageData);
			return $result;
		}

		Activity::log(array(
			'contentID'   => $role->id,
			'contentType' => 'Role',
			'description' => 'Deleted a Role',
			'details'     => 'Role: '.$role->name,
		));

		$result['resultType'] = "Success";
		$result['message']    = Lang::get('fractal::messages.successDeleted', array('item' => '<strong>'.$role->name.'</strong>'));

		$role->delete();

		return $result;
	}

}