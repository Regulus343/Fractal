<?php namespace Regulus\Fractal\Controllers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

use Fractal;

use Regulus\Fractal\Models\ContentPage;
use Regulus\Fractal\Models\ContentArea;
use Regulus\Fractal\Models\ContentLayoutTemplate;

use Regulus\ActivityLog\Activity;
use \Auth;
use \Form;
use \Format;
use \Site;

class PagesController extends BaseController {

	public function __construct()
	{
		parent::__construct();

		Fractal::setControllerPath($this);

		Site::set('section', 'Content');
		Site::set('subSection', 'Pages');
		Site::set('title', Fractal::lang('labels.pages'));

		//set content type and views location
		Fractal::setContentType('page', true);

		Fractal::addTrailItem('Pages', Fractal::getControllerPath());
	}

	public function index()
	{
		$data  = Fractal::setupPagination();
		$pages = ContentPage::getSearchResults($data);

		Fractal::setContentForPagination($pages);

		$data     = Fractal::setPaginationMessage(true);
		$messages = Fractal::getPaginationMessageArray();

		if (!count($pages))
			$pages = ContentPage::orderBy($data['sortField'], $data['sortOrder'])->paginate($data['itemsPerPage']);

		Fractal::addButton([
			'label' => Fractal::lang('labels.createPage'),
			'icon'  => 'glyphicon glyphicon-file',
			'uri'   => Fractal::uri('create', true),
		]);

		return View::make(Fractal::view('list'))
			->with('content', $pages)
			->with('messages', $messages);
	}

	public function search()
	{
		$data  = Fractal::setupPagination();
		$pages = ContentPage::getSearchResults($data);

		Fractal::setContentForPagination($pages);

		$data = Fractal::setPaginationMessage();

		if (!count($pages))
			$data['content'] = ContentPage::orderBy($data['sortField'], $data['sortOrder'])->paginate($data['itemsPerPage']);

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

		$layoutTagOptions = $this->getLayoutTagOptions();

		Fractal::addButton([
			'label' => Fractal::lang('labels.returnToPagesList'),
			'icon'  => 'glyphicon glyphicon-list',
			'uri'   => Fractal::uri('', true),
		]);

		return View::make(Fractal::view('form'))
			->with('layoutTagOptions', $layoutTagOptions);
	}

	public function store()
	{
		Form::setValidationRules(ContentPage::validationRules());

		$messages = [];
		if (Form::validated()) {
			$messages['success'] = Fractal::lang('messages.successCreated', ['item' => Fractal::langLowerA('labels.page')]);

			$input = Input::all();
			$input['user_id'] = Auth::user()->id;

			$page = ContentPage::createNew($input);

			//re-export menus to config array in case published status for page has changed
			Fractal::exportMenus();

			Activity::log([
				'contentId'   => $page->id,
				'contentType' => 'ContentPage',
				'action'      => 'Create',
				'description' => 'Created a Page',
				'details'     => 'Title: '.$page->title,
			]);

			return Redirect::to(Fractal::uri($page->slug.'/edit', true))
				->with('messages', $messages);
		} else {
			$messages['error'] = Fractal::lang('messages.errorGeneral');
		}

		return Redirect::to(Fractal::uri('create', true))
			->with('messages', $messages)
			->with('errors', Form::getErrors())
			->withInput();
	}

	public function edit($slug)
	{
		$page = ContentPage::findBySlug($slug);
		if (empty($page))
			return Redirect::to(Fractal::uri('', true))->with('messages', [
				'error' => Fractal::lang('messages.errorNotFound', ['item' => Fractal::langLower('labels.page')])
			]);

		Site::set('title', $page->title.' (Page)');
		Site::set('titleHeading', 'Update Page: <strong>'.Format::entities($page->title).'</strong>');
		Site::set('wysiwyg', true);

		$page->setDefaults(['contentAreas']);
		Form::setErrors();

		$layoutTagOptions = $this->getLayoutTagOptions($page->getLayoutTags());

		Fractal::addButtons([
			[
				'label' => Fractal::lang('labels.returnToPagesList'),
				'icon'  => 'glyphicon glyphicon-list',
				'uri'   => Fractal::uri('', true),
			],[
				'label' => Fractal::lang('labels.viewPage'),
				'icon'  => 'glyphicon glyphicon-file',
				'url'   => $page->getUrl(),
			]
		]);

		return View::make(Fractal::view('form'))
			->with('update', true)
			->with('id', $page->id)
			->with('pageUrl', $page->getUrl())
			->with('layoutTagOptions', $layoutTagOptions);
	}

	public function update($slug)
	{
		$page = ContentPage::findBySlug($slug);
		if (empty($page))
			return Redirect::to(Fractal::uri('', true))->with('messages', [
				'error' => Fractal::lang('messages.errorNotFound', ['item' => Fractal::langLower('labels.page')])
			]);

		$page->setValidationRules();

		$messages = [];
		if (Form::validated()) {
			$messages['success'] = Fractal::lang('messages.successUpdated', ['item' => Fractal::langLowerA('labels.page')]);

			$page->saveData();

			//re-export menus to config array in case published status for page has changed
			Fractal::exportMenus();

			Activity::log([
				'contentId'   => $page->id,
				'contentType' => 'ContentPage',
				'action'      => 'Update',
				'description' => 'Updated a Page',
				'details'     => 'Title: '.$page->title,
				'updated'     => true,
			]);

			return Redirect::to(Fractal::uri($slug.'/edit', true))
				->with('messages', $messages);
		} else {
			$messages['error'] = Fractal::lang('messages.errorGeneral');

			return Redirect::to(Fractal::uri($slug.'/edit', true))
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

		$page = ContentPage::find($id);
		if (empty($page))
			return $result;

		Activity::log([
			'contentId'   => $page->id,
			'contentType' => 'ContentPage',
			'action'      => 'Delete',
			'description' => 'Deleted a Page',
			'details'     => 'Title: '.$page->title,
		]);

		$result['resultType'] = "Success";
		$result['message']    = Fractal::lang('messages.successDeleted', ['item' => '<strong>'.$page->title.'</strong>']);

		$page->contentAreas()->sync([]);
		$page->delete();

		return $result;
	}

	public function view($slug = 'home')
	{
		$page = ContentPage::where('slug', $slug);

		if (Auth::isNot('admin'))
			$page->onlyPublished();

		$page = $page->first();

		if (empty($page))
			return Redirect::to('');

		Site::setMulti(['section', 'subSection', 'title'], $page->title);

		Site::resetTrailItems();
		Site::addTrailItem(Fractal::lang('labels.home'), '');

		if ($page->slug != "home")
			Site::addTrailItem($page->title, $page->slug);

		$page->logView();

		$messages = [];
		if (!$page->isPublished()) {
			if ($page->isPublishedFuture())
				$messages['info'] = Fractal::lang('messages.notPublishedUntil', [
					'item'     => strtolower(Fractal::lang('labels.page')),
					'dateTime' => $page->getPublishedDateTime(),
				]);
			else
				$messages['info'] = Fractal::lang('messages.notPublished', ['item' => Fractal::lang('labels.page')]);
		}

		return View::make(Config::get('fractal::pageView'))
			->with('page', $page)
			->with('messages', $messages);
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

	private function getLayoutTagOptions($layoutTagOptions = [])
	{
		if (Input::old()) {
			$layout = Input::old('layout');
			if (Input::old('layout_template_id') != "") {
				$template = ContentLayoutTemplate::find(Input::old('layout_template_id'));
				if (!empty($template))
					$layout = $template->layout;
			}

			$layoutTagOptions = Fractal::getLayoutTagsFromLayout($layout);
		}

		return $layoutTagOptions;
	}

	public function renderMarkdownContent()
	{
		return Fractal::renderMarkdownContent(Input::get('content'));
	}

	public function addContentArea($id = false)
	{
		$data = [
			'title'        => Fractal::lang('labels.addContentArea'),
			'pageId'       => $id,
			'contentAreas' => ContentArea::orderBy('title')->get(),
		];

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