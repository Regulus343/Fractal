<?php namespace Regulus\Fractal\Controllers\General;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

use Fractal;

use Form;
use Format;
use Site;

use Regulus\Fractal\Controllers\BaseController;

use Regulus\Fractal\Libraries\Reports;

class DashboardController extends BaseController {

	public function __construct()
	{
		parent::__construct();

		Site::setMulti(['section', 'subSection'], 'Home');
		Site::setTitle(Fractal::trans('labels.dashboard'));

		Fractal::setViewsLocation('core');
	}

	public function getIndex()
	{
		Site::set('title.hide', true);

		$reports = [
			'totalViewsMonth'     => Reports::totalViews('month'),
			'totalViewsYear'      => Reports::totalViews('year'),

			'uniqueViewsMonth'    => Reports::uniqueViews('month'),
			'uniqueViewsYear'     => Reports::uniqueViews('year'),

			'popularContentMonth' => Reports::popularContent('month'),
			'popularContentYear'  => Reports::popularContent('year'),
		];

		return View::make(Fractal::view('dashboard'))
			->with('reports', $reports);
	}

	public function getDeveloper($off = false)
	{
		if ($off == "off")
			Session::forget('developer');
		else
			Site::setDeveloper();

		return Redirect::to(Fractal::url())->with('messages', [
			'info' => Fractal::trans('messages.developer_mode_'.($off == "off" ? 'disabled' : 'enabled')),
		]);
	}

	public function getExportMenus()
	{
		Fractal::exportMenus();

		return Redirect::to(Fractal::url())->with('messages', [
			'info' => Fractal::trans('messages.menus_exported'),
		]);
	}

}