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

use Regulus\ActivityLog\Models\Activity;
use Auth;
use Form;
use Format;
use Site;

use Regulus\Fractal\Controllers\BaseController;

class PagesController extends BaseController {

	public function __construct()
	{
		parent::__construct();

		Fractal::setControllerPath($this);

		Site::set('section', 'Content');
		Site::set('subSection', 'Pages');
		Site::setTitle(Fractal::trans('labels.pages'));

		// set content type and views location
		Fractal::setContentType('page');

		Fractal::setViewsLocation('content.pages');

		Fractal::addTrailItem(Fractal::trans('labels.pages'), Fractal::getControllerPath());
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
			'label' => Fractal::trans('labels.createPage'),
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
		Site::setTitle(Fractal::trans('labels.create_item', ['item' => Fractal::transChoice('labels.page')]));
		Site::set('wysiwyg', true);

		Page::setDefaultsForNew();
		Form::setErrors();

		$layoutTagOptions = $this->getLayoutTagOptions();

		Fractal::addButton([
			'label' => Fractal::trans('labels.returnToPagesList'),
			'icon'  => 'glyphicon glyphicon-list',
			'uri'   => Fractal::uri('', true),
		]);

		Fractal::addTrailItem(Fractal::trans('labels.create'), Request::url());

		return View::make(Fractal::view('form'))
			->with('layoutTagOptions', $layoutTagOptions);
	}

	public function store()
	{
		Form::setValidationRules(Page::validationRules());

		$messages = [];
		if (Form::validated())
		{
			$messages['success'] = Fractal::trans('messages.successCreated', ['item' => Fractal::transLowerA('labels.page')]);

			$input = Input::all();
			$page  = Page::createNew($input);

			// re-export menus to config array in case published status for page has changed
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
			$messages['error'] = Fractal::trans('messages.errorGeneral');
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
				'error' => Fractal::trans('messages.errorNotFound', ['item' => Fractal::transLower('labels.page')])
			]);

		Site::setTitle($page->title.' ('.Fractal::trans('labels.page').')');
		Site::setHeading(Fractal::trans('labels.updatePage').': <strong>'.Format::entities($page->title).'</strong>');
		Site::set('wysiwyg', true);

		$page->setDefaults(['contentAreas']);
		Form::setErrors();

		$layoutTagOptions = $this->getLayoutTagOptions($page->getLayoutTags());

		Fractal::addButtons([
			[
				'label' => Fractal::trans('labels.returnToPagesList'),
				'icon'  => 'glyphicon glyphicon-list',
				'uri'   => Fractal::uri('', true),
			],[
				'label' => Fractal::trans('labels.viewPage'),
				'icon'  => 'glyphicon glyphicon-file',
				'url'   => $page->getUrl(),
			]
		]);

		Fractal::addTrailItem(Fractal::trans('labels.update'), Request::url());

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
				'error' => Fractal::trans('messages.errorNotFound', ['item' => Fractal::transLower('labels.page')])
			]);

		$page->setValidationRules();

		$messages = [];
		if (Form::validated())
		{
			$messages['success'] = Fractal::trans('messages.successUpdated', ['item' => Fractal::transLowerA('labels.page')]);

			$page->saveData();

			// re-export menus to config array in case published status for page has changed
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
			$messages['error'] = Fractal::trans('messages.errorGeneral');
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
			'message'    => Fractal::trans('messages.errorGeneral'),
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
		$result['message']    = Fractal::trans('messages.successDeleted', ['item' => '<strong>'.$page->title.'</strong>']);

		$page->contentAreas()->sync([]);
		$page->delete();

		return $result;
	}

	public function view($slug = 'home')
	{
		Site::set('public', true);

		$page = Page::findBySlug($slug, true)->onlyPublished(true)->first();

		// if page is not found, check slug without dashes
		if (empty($page))
		{
			$page = Page::findByDashlessSlug($slug, true)->onlyPublished(true)->first();

			// if page is found by dashless slug, redirect to URL with proper slug
			if (!empty($page))
				return Redirect::to($page->getUrl());
		}

		if (empty($page) || (!Auth::is('admin') && !$page->isPublished()))
			return Redirect::to('');

		Site::setMulti(['section', 'subSection'], $page->title);

		Site::resetTrailItems();
		Site::addTrailItem(Fractal::trans('labels.home'), '');

		if ($page->slug != "home")
		{
			Site::setTitle($page->title);
			Site::addTrailItem($page->title, $page->slug);
		} else {
			Site::setTitle(null);
		}

		$page->logView();

		$messages = [];
		if (!$page->isPublished())
		{
			if ($page->isPublishedFuture())
				$messages['info'] = Fractal::trans('messages.notPublishedUntil', [
					'item'     => strtolower(Fractal::trans('labels.page')),
					'dateTime' => $page->getPublishedDateTime(),
				]);
			else
				$messages['info'] = Fractal::trans('messages.notPublished', ['item' => Fractal::trans('labels.page')]);
		}

		Form::setErrors();

		return View::make(config('cms.page_view'))
			->with('page', $page)
			->with('messages', $messages);
	}

	public function layoutTags()
	{
		$input = Input::all();
		if (!isset($input['layout_template_id']) || !isset($input['layout']))
			return "";

		$layout = $input['layout'];
		if ($input['layout_template_id'] != "")
		{
			$template = LayoutTemplate::find($input['layout_template_id']);
			if (!empty($template))
				$layout = $template->layout;
		}

		return json_encode(Fractal::getLayoutTagsFromLayout($layout));
	}

	private function getLayoutTagOptions($layoutTagOptions = [])
	{
		if (Input::old())
		{
			$layout = Input::old('layout');

			if (Input::old('layout_template_id') != "")
			{
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
			'title'        => Fractal::trans('labels.addContentArea'),
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
		if ($contentArea)
		{
			if (!$contentArea->pages()->count())
			{
				$contentArea->delete();
				return "Success";
			}
		}

		return "Error";
	}

}