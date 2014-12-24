<?php namespace Regulus\Fractal\Controllers\Content;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

use Fractal;

use Regulus\Fractal\Models\Content\Page;
use Regulus\Fractal\Models\Content\Area;
use Regulus\Fractal\Models\Content\LayoutTemplate;

use Regulus\ActivityLog\Activity;
use \Auth;
use \Form;
use \Format;
use \Site;

use Regulus\Fractal\Controllers\BaseController;

class PagesController extends BaseController {

	public function __construct()
	{
		parent::__construct();

		Fractal::setControllerPath($this);

		Site::set('section', 'Content');
		Site::set('subSection', 'Pages');
		Site::set('title', Fractal::lang('labels.pages'));

		//set content type and views location
		Fractal::setContentType('page');

		Fractal::setViewsLocation('content.pages');

		Fractal::addTrailItem(Fractal::lang('labels.pages'), Fractal::getControllerPath());
	}

	public function index()
	{
		$data  = Fractal::setupPagination();
		$pages = Page::getSearchResults($data);

		Fractal::setContentForPagination($pages);

		$data     = Fractal::setPaginationMessage(true);
		$messages = Fractal::getPaginationMessageArray();

		if (!count($pages))
			$pages = Page::orderBy($data['sortField'], $data['sortOrder'])->paginate($data['itemsPerPage']);

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
		$pages = Page::getSearchResults($data);

		Fractal::setContentForPagination($pages);

		$data = Fractal::setPaginationMessage();

		if (!count($pages))
			$data['content'] = Page::orderBy($data['sortField'], $data['sortOrder'])->paginate($data['itemsPerPage']);

		$data['result']['pages']     = Fractal::getLastPage();
		$data['result']['tableBody'] = Fractal::createTable($data['content'], true);

		return $data['result'];
	}

	public function create()
	{
		Site::set('title', Fractal::lang('labels.createPage'));
		Site::set('wysiwyg', true);

		Page::setDefaultsForNew();
		Form::setErrors();

		$layoutTagOptions = $this->getLayoutTagOptions();

		Fractal::addButton([
			'label' => Fractal::lang('labels.returnToPagesList'),
			'icon'  => 'glyphicon glyphicon-list',
			'uri'   => Fractal::uri('', true),
		]);

		Fractal::addTrailItem(Fractal::lang('labels.create'), Request::url());

		return View::make(Fractal::view('form'))
			->with('layoutTagOptions', $layoutTagOptions);
	}

	public function store()
	{
		Form::setValidationRules(Page::validationRules());

		$messages = [];
		if (Form::validated()) {
			$messages['success'] = Fractal::lang('messages.successCreated', ['item' => Fractal::langLowerA('labels.page')]);

			$input = Input::all();
			$page  = Page::createNew($input);

			//re-export menus to config array in case published status for page has changed
			Fractal::exportMenus();

			Activity::log([
				'contentId'   => $page->id,
				'contentType' => 'Page',
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
		$page = Page::findBySlug($slug);
		if (empty($page))
			return Redirect::to(Fractal::uri('', true))->with('messages', [
				'error' => Fractal::lang('messages.errorNotFound', ['item' => Fractal::langLower('labels.page')])
			]);

		Site::set('title', $page->title.' ('.Fractal::lang('labels.page').')');
		Site::set('titleHeading', Fractal::lang('labels.updatePage').': <strong>'.Format::entities($page->title).'</strong>');
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

		Fractal::addTrailItem(Fractal::lang('labels.update'), Request::url());

		return View::make(Fractal::view('form'))
			->with('update', true)
			->with('id', $page->id)
			->with('pageUrl', $page->getUrl())
			->with('layoutTagOptions', $layoutTagOptions);
	}

	public function update($slug)
	{
		$page = Page::findBySlug($slug);
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
				'contentType' => 'Page',
				'action'      => 'Update',
				'description' => 'Updated a Page',
				'details'     => 'Title: '.$page->title,
				'updated'     => true,
			]);
		} else {
			$messages['error'] = Fractal::lang('messages.errorGeneral');
		}

		return Redirect::to(Fractal::uri($slug.'/edit', true))
			->with('messages', $messages)
			->with('errors', Form::getErrors())
			->withInput();
	}

	public function destroy($id)
	{
		$result = [
			'resultType' => 'Error',
			'message'    => Fractal::lang('messages.errorGeneral'),
		];

		$page = Page::find($id);
		if (empty($page))
			return $result;

		Activity::log([
			'contentId'   => $page->id,
			'contentType' => 'Page',
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
		Site::set('public', true);

		$page = Page::findBySlug($slug, true)->onlyPublished(true)->first();

		//if page is not found, check slug without dashes
		if (empty($page))
		{
			$page = Page::findByDashlessSlug($slug, true)->onlyPublished(true)->first();

			//if page is found by dashless slug, redirect to URL with proper slug
			if (!empty($page))
				return Redirect::to($page->getUrl());
		}

		if (empty($page) || (!Auth::is('admin') && !$page->isPublished()))
			return Redirect::to('');

		Site::setMulti(['section', 'subSection'], $page->title);

		Site::resetTrailItems();
		Site::addTrailItem(Fractal::lang('labels.home'), '');

		if ($page->slug != "home")
		{
			Site::set('title', $page->title);
			Site::addTrailItem($page->title, $page->slug);
		} else {
			Site::set('title', null);
		}

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

		Form::setErrors();

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
			$template = LayoutTemplate::find($input['layout_template_id']);
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
				$template = LayoutTemplate::find(Input::old('layout_template_id'));
				if (!empty($template))
					$layout = $template->layout;
			}

			$layoutTagOptions = Fractal::getLayoutTagsFromLayout($layout);
		}

		return $layoutTagOptions;
	}

	public function renderMarkdownContent()
	{
		return Fractal::renderMarkdownContent(Input::get('content'), ['insertViews' => false]);
	}

	public function addContentArea($id = null)
	{
		$data = [
			'title'        => Fractal::lang('labels.addContentArea'),
			'pageId'       => $id,
			'contentAreas' => Area::orderBy('title')->get(),
		];

		return Fractal::modalView('add_content_area', $data);
	}

	public function getContentArea($id = null)
	{
		return Area::find($id)->toJson();
	}

	public function deleteContentArea($id)
	{
		$contentArea = Area::find($id);
		if ($contentArea) {
			if (!$contentArea->pages()->count()) {
				$contentArea->delete();
				return "Success";
			}
		}

		return "Error";
	}

}