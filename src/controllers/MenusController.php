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
use Regulus\Identify\Identify as Auth;
use Regulus\SolidSite\SolidSite as Site;
use Regulus\TetraText\TetraText as Format;

class MenusController extends BaseController {

	public function __construct()
	{
		Site::set('section', 'Content');
		$subSection = "Menus";
		Site::setMulti(array('subSection', 'title'), $subSection);

		//set content type and views location
		Fractal::setContentType('menus', true);

		Site::set('defaultSorting', array('field' => 'cms'));
	}

	public function index()
	{
		$data = Fractal::setupPagination();

		$menus = Menu::orderBy($data['sortField'], $data['sortOrder']);
		if ($data['sortField'] != "id") $menus->orderBy('id', 'asc');
		if ($data['terms'] != "")
			$menus->where('name', 'like', $data['likeTerms']);

		$menus = $menus->paginate($data['itemsPerPage']);

		Fractal::setContentForPagination($menus);

		$data     = Fractal::setPaginationMessage();
		$messages = Fractal::getPaginationMessageArray();

		if (!count($menus))
			$menus = Menu::orderBy($data['sortField'], $data['sortOrder'])->paginate($data['itemsPerPage']);

		$defaults = array(
			'search' => $data['terms']
		);
		Form::setDefaults($defaults);

		return View::make(Fractal::view('list'))
			->with('content', $menus)
			->with('messages', $messages);
	}

	public function search()
	{
		$data = Fractal::setupPagination();

		$menus = Menu::orderBy($data['sortField'], $data['sortOrder']);
		if ($data['sortField'] != "id") $menus->orderBy('id', 'asc');
		if ($data['terms'] != "")
			$menus->where('name', 'like', $data['likeTerms']);

		$menus = $menus->paginate($data['itemsPerPage']);

		Fractal::setContentForPagination($menus);

		if (count($menus)) {
			$data = Fractal::setPaginationMessage();
		} else {
			$data['content'] = Menu::orderBy($data['sortField'], $data['sortOrder'])->paginate($data['itemsPerPage']);
			if ($data['terms'] == "") $data['result']['message'] = Lang::get('fractal::messages.searchNoTerms');
		}

		$data['result']['menus']     = Fractal::getLastPage();
		$data['result']['tableBody'] = Fractal::createTable($data['content'], true);

		return $data['result'];
	}

	public function create()
	{
		Site::set('title', 'Create Menu');

		return View::make(Fractal::view('form'));
	}

	public function store()
	{
		Site::set('title', 'Create Menu');

		Form::setValidationRules(Menu::validationRules());

		$messages = array();
		if (Form::validated()) {
			$messages['success'] = Lang::get('fractal::messages.successCreated', array('item' => Format::a('menu')));

			//save menu
			$menu = new Menu;
			$menu = static::fill($menu);

			Activity::log(array(
				'contentID'   => $menu->id,
				'contentType' => 'Menu',
				'description' => 'Created a Menu',
				'details'     => 'Name: '.$menu->name,
			));

			return Redirect::to(Fractal::uri('menus'))
				->with('messages', $messages);
		} else {
			$messages['error'] = Lang::get('fractal::messages.errorGeneral');
		}

		return View::make(Fractal::view('form'))
			->with('messages', $messages);
	}

	public function edit($id)
	{
		$menu = Menu::find($id);
		if (empty($menu))
			return Redirect::to(Fractal::uri('menus'))
				->with('messages', array('error' => Lang::get('fractal::messages.errorNotFound', array('item' => 'menu'))));

		Site::set('title', $menu->name.' (Menu)');
		Site::set('titleHeading', 'Update Menu: <strong>'.Format::entities($menu->name).'</strong>');

		Form::setDefaults($menu, array('items' => true));
		Form::setErrors();

		return View::make(Fractal::view('form'))->with('update', true)->with('menu', $menu);
	}

	public function update($id)
	{
		$menu = Menu::find($id);
		if (empty($menu))
			return Redirect::to(Fractal::uri('menus'))
				->with('messages', array('error' => Lang::get('fractal::messages.errorNotFound', array('item' => 'menu'))));

		Site::set('title', $menu->name.' (Menu)');
		Site::set('titleHeading', 'Update Menu: <strong>'.Format::entities($menu->name).'</strong>');

		Form::setValidationRules(Menu::validationRules($id));

		$messages = array();
		if (Form::validated()) {
			$messages['success'] = Lang::get('fractal::messages.successUpdated', array('item' => Format::a('menu')));

			//save menu
			$menu = static::fill($menu);

			Activity::log(array(
				'contentID'   => $menu->id,
				'contentType' => 'Menu',
				'description' => 'Created a Menu',
				'details'     => 'Name: '.$menu->name,
				'updated'     => true,
			));

			return Redirect::to(Fractal::uri('menus'))
				->with('messages', $messages);
		} else {
			$messages['error'] = Lang::get('fractal::messages.errorGeneral');

			return Redirect::to(Fractal::uri('menus/'.$id.'/edit'))
				->with('messages', $messages)
				->with('errors', Form::getErrors())
				->withInput();
		}
	}

	private function fill($menu)
	{
		$menu = Form::addValues($menu, Menu::fields());
		$menu->save();

		//add menu items
		$menuItems = Input::get('items');
		
		//add or update menu items in form
		$fields      = MenuItem::fields();
		$menuItemIds = array();

		foreach ($menuItems as $itemData) {
			$exists = false;
			$item   = new MenuItem;
			foreach ($menu->items as $itemListed) {
				if ($itemData['id'] == $itemListed->id) {
					$exists = true;
					$item   = $itemListed;
				}
			}

			if (!$exists)
				$item->menu_id = $menu->id;

			$item = Form::addValues($item, $fields);
			$item->save();

			$menuItemIds[] = $item->save();
		}

		//delete menu items that have been removed by form
		foreach ($menu->items as $item) {
			if (!in_array($item->id, $menuItemIds))
				$item->delete();
		}

		return $menu;
	}

	public function destroy($id)
	{
		$result = array(
			'resultType' => 'Error',
			'message'    => Lang::get('fractal::messages.errorGeneral'),
		);

		$menu = Menu::find($id);
		if (empty($menu))
			return $result;

		Activity::log(array(
			'contentID'   => $menu->id,
			'contentType' => 'Menu',
			'description' => 'Deleted a Menu',
			'details'     => 'Name: '.$menu->name,
		));

		$result['resultType'] = "Success";
		$result['message']    = Lang::get('fractal::messages.successDeleted', array('item' => '<strong>'.$menu->name.'</strong>'));

		$menu->items()->delete();
		$menu->delete();

		return $result;
	}

}