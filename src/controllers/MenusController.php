<?php namespace Regulus\Fractal;

use \BaseController;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL;

use Aquanode\Formation\Formation as Form;
use Regulus\TetraText\TetraText as Format;
use Regulus\SolidSite\SolidSite as Site;

class MenusController extends BaseController {

	public function __construct()
	{
		Site::set('section', 'Content');
		$subSection = "Menus";
		Site::setMulti(array('subSection', 'title'), $subSection);

		Fractal::setViewsLocation('menus');
	}

	public function index()
	{
		return View::make(Fractal::view('list'));
	}

	public function create()
	{
		return View::make(Fractal::view('form'));
	}

	public function edit($id)
	{
		$menu = Menu::with('items')->find($id);

		if (empty($menu) || (!empty($menu) && $menu->cms && !Site::developer()))
			return Redirect::to(Config::get('fractal::baseURI').'/menus')
				->with('messages', array('error' => 'The menu you selected was not found'));

		$defaults = $menu->toArray();
		foreach ($menu->items as $menuItem) {
			foreach ($menuItem->toArray() as $field => $value) {
				$defaults['item.'.$menuItem->id.'.'.$field] = $value;
			}

			$defaults['item.'.$menuItem->id.'.type'] = (int) $menuItem->page_id ? "Content Page" : "URI";
		}
		Form::setDefaults($defaults);

		return View::make(Fractal::view('form'))->with('menu', $menu);
	}

}