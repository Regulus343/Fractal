<?php namespace Regulus\Fractal\Controllers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

use Fractal;

use Regulus\Fractal\Models\Menu;

use Regulus\ActivityLog\Activity;
use \Auth;
use \Form;
use \Format;
use \Site;

class MenusController extends BaseController {

	public function __construct()
	{
		parent::__construct();

		Site::set('section', 'Content');
		Site::set('subSection', 'Menus');
		Site::set('title', Fractal::lang('labels.menus'));

		//set content type and views location
		Fractal::setContentType('menu', true);

		Site::set('defaultSorting', ['field' => 'cms']);

		Fractal::addTrailItem('Menus', 'menus');
	}

	public function index()
	{
		$data  = Fractal::setupPagination();
		$menus = Menu::getSearchResults($data);

		Fractal::setContentForPagination($menus);

		$data     = Fractal::setPaginationMessage(true);
		$messages = Fractal::getPaginationMessageArray();

		if (!count($menus))
			$menus = Menu::orderBy($data['sortField'], $data['sortOrder'])->paginate($data['itemsPerPage']);

		Fractal::addButton([
			'label' => Fractal::lang('labels.createMenu'),
			'icon'  => 'glyphicon glyphicon-list',
			'uri'   => 'menus/create',
		]);

		return View::make(Fractal::view('list'))
			->with('content', $menus)
			->with('messages', $messages);
	}

	public function search()
	{
		$data  = Fractal::setupPagination();
		$menus = Menu::getSearchResults($data);

		Fractal::setContentForPagination($menus);

		$data = Fractal::setPaginationMessage();

		if (!count($menus))
			$data['content'] = Menu::orderBy($data['sortField'], $data['sortOrder'])->paginate($data['itemsPerPage']);

		$data['result']['pages']     = Fractal::getLastPage();
		$data['result']['tableBody'] = Fractal::createTable($data['content'], true);

		return $data['result'];
	}

	public function create()
	{
		Site::set('title', 'Create Menu');

		Form::setErrors();

		Fractal::addButton([
			'label' => Fractal::lang('labels.returnToMenusList'),
			'icon'  => 'glyphicon glyphicon-list',
			'uri'   => 'menus',
		]);

		return View::make(Fractal::view('form'));
	}

	public function store()
	{
		Form::setValidationRules(Menu::validationRules());

		$messages = [];
		if (Form::validated()) {
			$messages['success'] = Fractal::lang('messages.successCreated', ['item' => Fractal::langLowerA('labels.menu')]);

			$menu = Menu::createNew();

			//re-export menus to config array
			Fractal::exportMenus();

			Activity::log([
				'contentId'   => $menu->id,
				'contentType' => 'Menu',
				'action'      => 'Create',
				'description' => 'Created a Menu',
				'details'     => 'Name: '.$menu->name,
			]);

			return Redirect::to(Fractal::uri('menus'))
				->with('messages', $messages);
		} else {
			$messages['error'] = Fractal::lang('messages.errorGeneral');
		}

		return Redirect::to(Fractal::uri('menus/create'))
			->with('messages', $messages)
			->with('errors', Form::getErrors())
			->withInput();
	}

	public function edit($id)
	{
		$menu = Menu::find($id);
		if (empty($menu))
			return Redirect::to(Fractal::uri('menus'))->with('messages', [
				'error' => Fractal::lang('messages.errorNotFound', ['item' => Fractal::langLower('labels.menu')])
			]);

		Site::set('title', $menu->name.' (Menu)');
		Site::set('titleHeading', 'Update Menu: <strong>'.Format::entities($menu->name).'</strong>');

		$menu->setDefaults(['items']);
		Form::setErrors();

		Fractal::addButton([
			'label' => Fractal::lang('labels.returnToMenusList'),
			'icon'  => 'glyphicon glyphicon-list',
			'uri'   => 'menus',
		]);

		return View::make(Fractal::view('form'))
			->with('update', true)
			->with('menu', $menu);
	}

	public function update($id)
	{
		$menu = Menu::find($id);
		if (empty($menu))
			return Redirect::to(Fractal::uri('menus'))->with('messages', [
				'error' => Fractal::lang('messages.errorNotFound', ['item' => Fractal::langLower('labels.menu')])
			]);

		Form::setValidationRules(Menu::validationRules($id));

		$messages = [];
		if (Form::validated()) {
			$messages['success'] = Fractal::lang('messages.successUpdated', ['item' => Fractal::langLowerA('labels.menu')]);

			$menu->saveData();

			//re-export menus to config array
			Fractal::exportMenus();

			Activity::log([
				'contentId'   => $menu->id,
				'contentType' => 'Menu',
				'action'      => 'Update',
				'description' => 'Updated a Menu',
				'details'     => 'Name: '.$menu->name,
				'updated'     => true,
			]);

			return Redirect::to(Fractal::uri('menus'))
				->with('messages', $messages);
		} else {
			$messages['error'] = Fractal::lang('messages.errorGeneral');

			return Redirect::to(Fractal::uri('menus/'.$id.'/edit'))
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

		$menu = Menu::find($id);
		if (empty($menu))
			return $result;

		Activity::log([
			'contentId'   => $menu->id,
			'contentType' => 'Menu',
			'action'      => 'Delete',
			'description' => 'Deleted a Menu',
			'details'     => 'Name: '.$menu->name,
		]);

		$result['resultType'] = "Success";
		$result['message']    = Fractal::lang('messages.successDeleted', ['item' => '<strong>'.$menu->name.'</strong>']);

		$menu->items()->delete();
		$menu->delete();

		return $result;
	}

}