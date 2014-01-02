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
		return View::make(Fractal::view('list'))->with('contentType', 'pages');
	}

	public function create()
	{
		Site::set('title', 'Create Page');
		Site::set('wysiwyg', true);

		$defaults = array('active' => true);
		Form::setDefaults($defaults);

		return View::make(Fractal::view('form'));
	}

	public function store()
	{
		Site::set('title', 'Create Page');
		Site::set('wysiwyg', true);

		Form::setValidationRules(ContentPage::validationRules());

		$messages = array();
		if (Form::validated()) {
			$messages['success'] = Lang::get('fractal::messages.successCreated', array('item' => Format::a('page')));

			$page = new ContentPage;
			$page->title            = ucfirst(trim(Input::get('title')));
			$page->slug             = Format::uniqueSlug(Input::get('slug'), 'content_pages');
			$page->content          = trim(Input::get('content'));
			$page->set_side_content = Input::get('set_side_content') ? true : false;
			$page->side_content     = Input::get('set_side_content') ? trim(Input::get('side_content')) : '';
			$page->user_id          = Auth::user()->id;
			$page->active           = Form::value('active', 'checkbox');
			$page->save();

			Activity::log(array(
				'contentID'   => $page->id,
				'contentType' => 'ContentPage',
				'description' => 'Created a Page',
				'details'     => 'Title: '.$page->title,
			));

			return Redirect::to(Fractal::uri('pages'))
				->with('messages', $messages);
		} else {
			$messages['error'] = Lang::get('fractal::messages.errorGeneral');
		}

		return View::make(Fractal::view('form'))
			->with('messages', $messages);
	}

	public function edit($slug)
	{
		$page = ContentPage::findBySlug($slug);
		if (empty($page))
			return Redirect::to(Fractal::uri('pages'))
				->with('messages', array('error' => Lang::get('fractal::messages.errorNotFound', array('item' => 'page'))));

		Site::set('title', $page->title.' (Page)');
		Site::set('titleHeading', 'Update Page: <strong>'.Format::entities($page->title).'</strong>');
		Site::set('wysiwyg', true);

		Form::setDefaults($page);
		Form::setErrors();

		return View::make(Fractal::view('form'))->with('update', true);
	}

	public function update($slug)
	{
		$page = ContentPage::findBySlug($slug);
		if (empty($page))
			return Redirect::to(Fractal::uri('pages'))
				->with('messages', array('error' => Lang::get('fractal::messages.errorNotFound', array('item' => 'page'))));

		Site::set('title', $page->title.' (Page)');
		Site::set('titleHeading', 'Update Page: <strong>'.Format::entities($page->title).'</strong>');
		Site::set('wysiwyg', true);

		Form::setValidationRules(ContentPage::validationRules());

		$messages = array();
		if (Form::validated()) {
			$messages['success'] = Lang::get('fractal::messages.successUpdated', array('item' => Format::a('page')));

			$page->title            = ucfirst(trim(Input::get('title')));
			$page->slug             = Format::uniqueSlug(Input::get('slug'), 'content_pages', 'slug', $page->id);
			$page->content          = trim(Input::get('content'));
			$page->set_side_content = Input::get('set_side_content') ? true : false;
			$page->side_content     = Input::get('set_side_content') ? trim(Input::get('side_content')) : '';
			$page->active           = Form::value('active', 'checkbox');
			$page->save();

			Activity::log(array(
				'contentID'   => $page->id,
				'contentType' => 'ContentPage',
				'description' => 'Created a Page',
				'details'     => 'Title: '.$page->title,
				'updated'     => true,
			));

			return Redirect::to(Fractal::uri('pages'))
				->with('messages', $messages);
		} else {
			$messages['error'] = Lang::get('fractal::messages.errorGeneral');

			return Redirect::to(Fractal::uri('pages/'.$slug.'/edit'))
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

		$page = ContentPage::find($id);
		if (empty($page))
			return $result;

		Activity::log(array(
			'contentID'   => $page->id,
			'contentType' => 'ContentPage',
			'description' => 'Deleted a Page',
			'details'     => 'Title: '.$page->title,
		));

		$result['resultType'] = "Success";
		$result['message']    = Lang::get('fractal::messages.successDeleted', array('item' => '<strong>'.$page->title.'</strong>'));

		$page->delete();

		return $result;
	}

	public function view($slug = '')
	{
		$page = ContentPage::findBySlug($slug);
		if (empty($page)) return Redirect::to('home');

		Site::set('title', $page->title);

		return View::make(Config::get('fractal::pageView'))->with('page', $page);
	}

}