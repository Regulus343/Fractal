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

class PagesController extends BaseController {

	public function __construct()
	{
		Site::set('section', 'Content');
		$subSection = "Pages";
		Site::setMulti(array('subSection', 'title'), $subSection);

		//set content type and views location
		Fractal::setContentType('pages', true);
	}

	public function index()
	{
		$data = Fractal::setupPagination();

		$pages = ContentPage::orderBy($data['sortField'], $data['sortOrder']);
		if ($data['sortField'] != "id") $pages->orderBy('id', 'asc');
		if ($data['terms'] != "") {
			$pages->where(function($query) use ($data) {
				$query
					->where('title', 'like', $data['likeTerms'])
					->orWhere('slug', 'like', $data['likeTerms'])
					->orWhere('content', 'like', $data['likeTerms'])
					->orWhere('side_content', 'like', $data['likeTerms']);
			});
		}
		$pages = $pages->paginate($data['itemsPerPage']);

		Fractal::setContentForPagination($pages);

		$data     = Fractal::setPaginationMessage();
		$messages = Fractal::getPaginationMessageArray();

		if (!count($pages))
			$pages = ContentPage::orderBy($data['sortField'], $data['sortOrder'])->paginate($data['itemsPerPage']);

		$defaults = array(
			'search' => $data['terms']
		);
		Form::setDefaults($defaults);

		return View::make(Fractal::view('list'))
			->with('content', $pages)
			->with('messages', $messages);
	}

	public function search()
	{
		$data = Fractal::setupPagination();

		$pages = ContentPage::orderBy($data['sortField'], $data['sortOrder']);
		if ($data['sortField'] != "id") $pages->orderBy('id', 'asc');
		if ($data['terms'] != "") {
			$pages->where(function($query) use ($data) {
				$query
					->where('title', 'like', $data['likeTerms'])
					->orWhere('slug', 'like', $data['likeTerms'])
					->orWhere('content', 'like', $data['likeTerms'])
					->orWhere('side_content', 'like', $data['likeTerms']);
			});
		}
		$pages = $pages->paginate($data['itemsPerPage']);

		Fractal::setContentForPagination($pages);

		if (count($pages)) {
			$data = Fractal::setPaginationMessage();
		} else {
			$data['content'] = ContentPage::orderBy($data['sortField'], $data['sortOrder'])->paginate($data['itemsPerPage']);
			if ($data['terms'] == "") $data['result']['message'] = Lang::get('fractal::messages.searchNoTerms');
		}

		$data['result']['pages']     = Fractal::getLastPage();
		$data['result']['tableBody'] = Fractal::createTable($data['content'], true);

		return $data['result'];
	}

	public function create()
	{
		Site::set('title', 'Create Page');
		Site::set('wysiwyg', true);

		$defaults = array('active' => true);
		Form::setDefaults($defaults);

		return View::make(Fractal::view('form'))->with('contentAreas', array());
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
			->with('messages', $messages)
			->with('contentAreas', array());
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

		Form::setDefaults($page, array('content_areas' => true));
		Form::setErrors();

		$contentAreas = array();
		if (Input::old('contentAreas')) {
			$contentAreasPosted = Input::old('contentAreas');
			foreach ($contentAreasPosted as $n => $contentArea) {
				$contentAreas[($n + 1)] = $contentArea;
			}
		} else {
			foreach ($page->contentAreas as $n => $contentArea) {
				$contentAreas[($n + 1)] = $contentArea->toArray();
			}
		}

		return View::make(Fractal::view('form'))
			->with('update', true)
			->with('contentAreas', $contentAreas);
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