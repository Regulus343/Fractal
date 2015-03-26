<?php namespace Regulus\Fractal\Controllers\Content;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

use Fractal;

use Regulus\Fractal\Models\Content\Menu;

use Regulus\ActivityLog\Models\Activity;
use Auth;
use Form;
use Format;
use Site;

use Regulus\Fractal\Controllers\BaseController;

class MenusController extends BaseController {

	public function __construct()
	{
		parent::__construct();

		Fractal::setControllerPath($this);

		Site::set('section', 'Content');
		Site::set('subSection', 'Menus');
		Site::setTitle(Fractal::transChoice('labels.menu'));

		// set content type and views location
		Fractal::setContentType('menu');

		Fractal::setViewsLocation('content.menus');

		Fractal::addTrailItem(Fractal::transChoice('labels.menu', 2), Fractal::getControllerPath());

		Site::set('defaultSorting', ['field' => 'cms']);
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
			'label' => Fractal::trans('labels.create_item', ['item' => Fractal::transChoice('labels.menu')]),
			'icon'  => 'th-list',
			'uri'   => Fractal::uri('create', true),
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
		Site::setTitle(Fractal::trans('labels.create_item', ['item' => Fractal::transChoice('menu')]));

		Form::setErrors();

		Fractal::addButton([
			'label' => Fractal::trans('labels.return_to_items_list', ['items' => Fractal::transChoice('labels.menu', 2)]),
			'icon'  => 'list',
			'uri'   => Fractal::uri('', true),
		]);

		Fractal::addTrailItem(Fractal::trans('labels.create'), Request::url());

		return View::make(Fractal::view('form'));
	}

	public function store()
	{
		Form::setValidationRules(Menu::validationRules());

		$messages = [];
		if (Form::isValid())
		{
			$messages['success'] = Fractal::trans('messages.success.created', ['item' => Fractal::transChoiceLowerA('labels.menu')]);

			$menu = Menu::createNew();

			// re-export menus to config array
			Fractal::exportMenus();

			Activity::log([
				'contentId'   => $menu->id,
				'contentType' => 'Menu',
				'action'      => 'Create',
				'description' => 'Created a Menu',
				'details'     => 'Name: '.$menu->name,
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
		$menu = Menu::find($id);
		if (empty($menu))
			return Redirect::to(Fractal::uri('', true))->with('messages', [
				'error' => Fractal::trans('messages.errors.not_found', ['item' => Fractal::transChoiceLower('labels.menu')])
			]);

		Site::setTitle($menu->name.' ('.Fractal::transChoice('labels.menu').')');
		Site::setHeading(Fractal::trans('labels.update_item', ['item' => Fractal::transChoice('labels.menu')]).': <strong>'.Format::entities($menu->name).'</strong>');

		$menu->setDefaults(['items']);
		Form::setErrors();

		Fractal::addButton([
			'label' => Fractal::trans('labels.return_to_items_list', ['items' => Fractal::transChoice('labels.menu', 2)]),
			'icon'  => 'list',
			'uri'   => 'menus',
		]);

		Fractal::addTrailItem(Fractal::trans('labels.update'), Request::url());

		return View::make(Fractal::view('form'))
			->with('update', true)
			->with('menu', $menu);
	}

	public function update($id)
	{
		$menu = Menu::find($id);
		if (empty($menu))
			return Redirect::to(Fractal::uri('', true))->with('messages', [
				'error' => Fractal::trans('messages.errors.not_found', ['item' => Fractal::transChoiceLower('labels.menu')])
			]);

		Form::setValidationRules(Menu::validationRules($id));

		$messages = [];
		if (Form::isValid())
		{
			$messages['success'] = Fractal::trans('messages.success.updated', ['item' => Fractal::transChoiceLowerA('labels.menu')]);

			$menu->saveData();

			// re-export menus to config array
			Fractal::exportMenus();

			Activity::log([
				'contentId'   => $menu->id,
				'contentType' => 'Menu',
				'action'      => 'Update',
				'description' => 'Updated a Menu',
				'details'     => 'Name: '.$menu->name,
				'updated'     => true,
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
		$result['message']    = Fractal::trans('messages.success.deleted', ['item' => '<strong>'.$menu->name.'</strong>']);

		$menu->items()->delete();
		$menu->delete();

		return $result;
	}

}