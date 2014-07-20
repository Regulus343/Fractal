<?php namespace Regulus\Fractal;

use \BaseController;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

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
		Fractal::setContentType('page', true);
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
					->orWhere('slug', 'like', $data['likeTerms']);
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
					->orWhere('slug', 'like', $data['likeTerms']);
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

		ContentPage::setDefaultsForNew();
		Form::setErrors();

		return View::make(Fractal::view('form'))
			->with('layoutTagOptions', array());
	}

	public function store()
	{
		Site::set('title', 'Create Page');
		Site::set('wysiwyg', true);

		Form::setValidationRules(ContentPage::validationRules());

		$messages = array();
		if (Form::validated()) {
			$messages['success'] = Lang::get('fractal::messages.successCreated', array('item' => Format::a('page')));

			$page = ContentPage::createNew();

			Activity::log(array(
				'contentId'   => $page->id,
				'contentType' => 'ContentPage',
				'action'      => 'Create',
				'description' => 'Created a Page',
				'details'     => 'Title: '.$page->title,
			));

			return Redirect::to(Fractal::uri('pages'))
				->with('messages', $messages);
		} else {
			$messages['error'] = Lang::get('fractal::messages.errorGeneral');
		}

		return Redirect::to(Fractal::uri('pages/create'))
			->with('messages', $messages)
			->with('errors', Form::getErrors())
			->withInput();
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

		$page->setDefaults(array('contentAreas'));
		Form::setErrors();

		return View::make(Fractal::view('form'))
			->with('update', true)
			->with('id', $page->id)
			->with('pageUrl', $page->getUrl())
			->with('layoutTagOptions', Form::simpleOptions($page->getLayoutTags(true)));
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

		$page->setDefaults(array('contentAreas'));
		$page->setValidationRules();

		$messages = array();
		if (Form::validated()) {
			$messages['success'] = Lang::get('fractal::messages.successUpdated', array('item' => Format::a('page')));

			$page->saveData();

			Activity::log(array(
				'contentId'   => $page->id,
				'contentType' => 'ContentPage',
				'action'      => 'Update',
				'description' => 'Updated a Page',
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
			'contentId'   => $page->id,
			'contentType' => 'ContentPage',
			'action'      => 'Delete',
			'description' => 'Deleted a Page',
			'details'     => 'Title: '.$page->title,
		));

		$result['resultType'] = "Success";
		$result['message']    = Lang::get('fractal::messages.successDeleted', array('item' => '<strong>'.$page->title.'</strong>'));

		$page->contentAreas()->sync(array());
		$page->delete();

		return $result;
	}

	public function view($slug = 'home')
	{
		$page = ContentPage::where('slug', $slug)->where('active', true)->where('activated_at', '<=', date('Y-m-d H:i:s'))->first();
		if (empty($page))
			return Redirect::to('');

		Site::setMulti(array('section', 'subSection', 'title'), $page->title);
		Site::set('menus', 'Front');

		$page->logView();

		return View::make(Config::get('fractal::pageView'))->with('page', $page);
	}

	public function layoutTags()
	{
		$input = Input::all();
		if (!isset($input['layout_template_id']) || !isset($input['layout']))
			return "";

		$layout = $input['layout'];
		if ($input['layout_template_id'] != "") {
			$template = ContentLayoutTemplate::find($input['layout_template_id']);
			if (!empty($template))
				$layout = $template->layout;
		}

		return json_encode(Fractal::getLayoutTagsFromLayout($layout));
	}

	public function renderMarkdownContent()
	{
		return Fractal::renderMarkdownContent(Input::get('content'));
	}

	public function addContentArea($id = false)
	{
		$data = array(
			'title'        => Lang::get('fractal::labels.addContentArea'),
			'pageId'       => $id,
			'contentAreas' => ContentArea::orderBy('title')->get(),
		);

		return Fractal::modalView('add_content_area', $data);
	}

	public function getContentArea($id = false)
	{
		return ContentArea::find($id)->toJson();
	}

	public function deleteContentArea($id)
	{
		$contentArea = ContentArea::find($id);
		if ($contentArea) {
			if (!$contentArea->contentPages()->count()) {
				$contentArea->delete();
				return "Success";
			}
		}

		return "Error";
	}

}