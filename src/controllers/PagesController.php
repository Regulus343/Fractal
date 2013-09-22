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
use Regulus\TetraText\TetraText as Format;
use Regulus\SolidSite\SolidSite as Site;

class PagesController extends BaseController {

	public function __construct()
	{
		Site::set('section', 'Content');
		$subSection = "Pages";
		Site::setMulti(array('subSection', 'title'), $subSection);

		Fractal::setViewsLocation('pages');
	}

	public function index()
	{
		return View::make(Fractal::view('list'));
	}

	public function create()
	{
		Site::set('title', 'Create Page');
		Site::set('wysiwyg', true);

		$defaults = array('active' => true);
		Form::setDefaults($defaults);

		return View::make(Fractal::view('form'))->with('update', false);
	}

	public function store()
	{
		Site::set('title', 'Create Page');
		Site::set('wysiwyg', true);

		Form::setValidationRules(Page::validationRules());

		$messages = array();
		if (Form::validated()) {
			$messages['success'] = Lang::get('fractal::messages.successCreated', array('item' => Format::a('page')));

			$page = new Page;
			$page->title            = ucfirst(trim(Input::get('title')));
			$page->slug             = Format::uniqueSlug(Input::get('slug'), 'content_pages');
			$page->content          = trim(Input::get('content'));
			$page->set_side_content = Input::get('set_side_content') ? true : false;
			$page->side_content     = Input::get('set_side_content') ? trim(Input::get('side_content')) : '';
			$page->active           = Form::value('active', 'checkbox');
			$page->save();

			Activity::log(array(
				'contentID'   => $page->id,
				'contentType' => 'Page',
				'description' => 'Created a Page',
				'details'     => 'Title: '.$page->title,
			));

			return Redirect::to(Fractal::uri('pages'))
				->with('messages', $messages);
		} else {
			$messages['error']   = Lang::get('fractal::messages.errorGeneral');
		}

		return View::make(Fractal::view('form'))
			->with('update', true)
			->with('messages', $messages);
	}

	public function edit($slug)
	{
		$page = Page::bySlug($slug);
		if (empty($page))
			return Redirect::to(Fractal::uri('pages'))
				->with('messages', array('error' => 'The page you selected was not found'));

		Site::set('title', $page->title.' (Page)');
		Site::set('titleHeading', 'Update Page: <strong>'.Format::entities($page->title).'</strong>');
		Site::set('wysiwyg', true);

		Form::setDefaults($page);

		return View::make(Fractal::view('form'))->with('update', true);
	}

	public function update($slug)
	{
		$page = Page::bySlug($slug);

		if (empty($page))
			return Redirect::to(Fractal::uri('pages'))
				->with('messages', array('error' => 'The page you selected was not found'));

		Site::set('title', $page->title.' (Page)');
		Site::set('titleHeading', 'Update Page: <strong>'.Format::entities($page->title).'</strong>');
		Site::set('wysiwyg', true);

		Form::setValidationRules(Page::validationRules());

		$messages = array();
		if (Form::validated()) {
			$messages['success'] = Lang::get('fractal::messages.successUpdated', array('item' => Format::a('page')));

			$page->title            = ucfirst(trim(Input::get('title')));
			$page->slug             = Format::uniqueSlug(Input::get('slug'), 'content_pages', $page->id);
			$page->content          = trim(Input::get('content'));
			$page->set_side_content = Input::get('set_side_content') ? true : false;
			$page->side_content     = Input::get('set_side_content') ? trim(Input::get('side_content')) : '';
			$page->active           = Form::value('active', 'checkbox');
			$page->save();

			Activity::log(array(
				'contentID'   => $page->id,
				'contentType' => 'Page',
				'description' => 'Created a Page',
				'details'     => 'Title: '.$page->title,
				'updated'     => true,
			));

			return Redirect::to(Fractal::uri('pages'))
				->with('messages', $messages);
		} else {
			$messages['error']   = Lang::get('fractal::messages.errorGeneral');
		}

		return View::make(Fractal::view('form'))
			->with('update', true)
			->with('messages', $messages);
	}

	public function view($slug = '')
	{
		$page = Page::bySlug($slug);
		if (empty($page)) return Redirect::to('home');

		Site::set('title', $page->title);

		return View::make(Config::get('fractal::pageView'))->with('page', $page);
	}

}