<?php namespace Regulus\Fractal\Controllers\Blogs;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

use Fractal;

use Regulus\Fractal\Models\BlogArticle;
use Regulus\Fractal\Models\BlogContentArea;
use Regulus\Fractal\Models\ContentLayoutTemplate;
use Regulus\Fractal\Models\ContentFile;
use Regulus\Fractal\Models\MediaItem;

use Regulus\ActivityLog\Activity;
use \Auth;
use \Form;
use \Format;
use \Site;

class ArticlesController extends BlogsController {

	public function __construct()
	{
		parent::__construct();

		Fractal::setControllerPath($this);

		Site::set('section', 'Blogs');
		Site::set('subSection', 'Articles');
		Site::set('title', Fractal::lang('labels.blogArticles'));

		//set content type and views location
		Fractal::setContentType('blog-article', true);

		Fractal::setViewsLocation('blogs.articles');

		Fractal::addTrailItem(Fractal::lang('labels.articles'), Fractal::getControllerPath());
	}

	public function index()
	{
		$data     = Fractal::setupPagination();
		$articles = BlogArticle::getSearchResults($data);

		Fractal::setContentForPagination($articles);

		$data     = Fractal::setPaginationMessage(true);
		$messages = Fractal::getPaginationMessageArray();

		if (!count($articles))
			$articles = BlogArticle::orderBy($data['sortField'], $data['sortOrder'])->paginate($data['itemsPerPage']);

		Fractal::addButton([
			'label' => Fractal::lang('labels.createArticle'),
			'icon'  => 'glyphicon glyphicon-file',
			'uri'   => Fractal::uri('create', true),
		]);

		return View::make(Fractal::view('list'))
			->with('content', $articles)
			->with('messages', $messages);
	}

	public function search()
	{
		$data     = Fractal::setupPagination();
		$articles = BlogArticle::getSearchResults($data);

		Fractal::setContentForPagination($articles);

		$data = Fractal::setPaginationMessage();

		if (!count($articles))
			$data['content'] = BlogArticle::orderBy($data['sortField'], $data['sortOrder'])->paginate($data['itemsPerPage']);

		$data['result']['pages']     = Fractal::getLastPage();
		$data['result']['tableBody'] = Fractal::createTable($data['content'], true);

		return $data['result'];
	}

	public function create()
	{
		Site::set('title', Fractal::lang('labels.createArticle'));
		Site::set('wysiwyg', true);

		BlogArticle::setDefaultsForNew();
		Form::setErrors();

		$layoutTagOptions = $this->getLayoutTagOptions();

		Fractal::addButton([
			'label' => Fractal::lang('labels.returnToArticlesList'),
			'icon'  => 'glyphicon glyphicon-list',
			'uri'   => Fractal::uri('', true),
		]);

		Fractal::addTrailItem(Fractal::lang('labels.create'), Request::url());

		return View::make(Fractal::view('form'))
			->with('layoutTagOptions', $layoutTagOptions);
	}

	public function store()
	{
		Form::setValidationRules(BlogArticle::validationRules());

		$messages = [];
		if (Form::validated()) {
			$messages['success'] = Fractal::lang('messages.successCreated', ['item' => Fractal::langLowerA('labels.article')]);

			$input = Input::all();
			$input['user_id'] = Auth::user()->id;

			$article = BlogArticle::createNew($input);

			//re-export menus to config array in case published status for article has changed
			Fractal::exportMenus();

			Activity::log([
				'contentId'   => $article->id,
				'contentType' => 'BlogArticle',
				'action'      => 'Create',
				'description' => 'Created an Article',
				'details'     => 'Title: '.$article->title,
			]);

			return Redirect::to(Fractal::uri('', true))
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
		$article = BlogArticle::findBySlug($slug);
		if (empty($article))
			return Redirect::to(Fractal::uri('pages'))->with('messages', [
				'error' => Fractal::lang('messages.errorNotFound', ['item' => Fractal::langLower('labels.article')])
			]);

		Site::set('title', $article->title.' ('.Fractal::lang('labels.article').')');
		Site::set('titleHeading', Fractal::lang('labels.updateArticle').': <strong>'.Format::entities($article->title).'</strong>');
		Site::set('wysiwyg', true);

		$article->setDefaults([
			'contentAreas' => true,
			'categories'   => 'category_id',
		]);
		Form::setErrors();

		$layoutTagOptions = $this->getLayoutTagOptions($article->getLayoutTags());

		Fractal::addButtons([
			[
				'label' => Fractal::lang('labels.returnToArticlesList'),
				'icon'  => 'glyphicon glyphicon-list',
				'uri'   => Fractal::uri('', true),

			],[
				'label' => Fractal::lang('labels.viewArticle'),
				'icon'  => 'glyphicon glyphicon-file',
				'url'   => $article->getUrl(),
			]
		]);

		Fractal::addTrailItem(Fractal::lang('labels.update'), Request::url());

		return View::make(Fractal::view('form'))
			->with('update', true)
			->with('id', $article->id)
			->with('article', $article)
			->with('articleUrl', $article->getUrl())
			->with('layoutTagOptions', $layoutTagOptions);
	}

	public function update($slug)
	{
		$article = BlogArticle::findBySlug($slug);
		if (empty($article))
			return Redirect::to(Fractal::uri('pages'))->with('messages', [
				'error' => Fractal::lang('messages.errorNotFound', ['item' => Fractal::langLower('labels.article')])
			]);

		$article->setValidationRules();

		$messages = [];
		if (Form::validated()) {
			$messages['success'] = Fractal::lang('messages.successUpdated', ['item' => Fractal::langLowerA('labels.article')]);

			$article->saveData();

			Activity::log([
				'contentId'   => $article->id,
				'contentType' => 'BlogArticle',
				'action'      => 'Update',
				'description' => 'Updated an Article',
				'details'     => 'Title: '.$article->title,
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

		$article = BlogArticle::find($id);
		if (empty($article))
			return $result;

		Activity::log([
			'contentId'   => $article->id,
			'contentType' => 'BlogArticle',
			'action'      => 'Delete',
			'description' => 'Deleted an Article',
			'details'     => 'Title: '.$article->title,
		]);

		$result['resultType'] = "Success";
		$result['message']    = Fractal::lang('messages.successDeleted', ['item' => '<strong>'.$article->title.'</strong>']);

		$article->contentAreas()->sync([]);
		$article->delete();

		return $result;
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

	public function addContentArea($id = null)
	{
		$data = [
			'title'        => Fractal::lang('labels.addContentArea'),
			'articleId'    => $id,
			'contentAreas' => BlogContentArea::orderBy('title')->get(),
		];

		return Fractal::modalView('add_content_area', $data);
	}

	public function getContentArea($id = null)
	{
		return BlogContentArea::find($id)->toJson();
	}

	public function deleteContentArea($id)
	{
		$contentArea = BlogContentArea::find($id);
		if ($contentArea) {
			if (!$contentArea->contentPages()->count()) {
				$contentArea->delete();
				return "Success";
			}
		}

		return "Error";
	}

	public function selectThumbnailImage($id = null)
	{
		$defaultThumbnailImageType = "File";
		$selectedFileId            = null;
		$selectedMediaItemId       = null;

		if (!is_null($id)) {
			$article = BlogArticle::find($id);

			if (!empty($article) && !is_null($article->thumbnail_image_type))
			{
				$defaultThumbnailImageType = $article->thumbnail_image_type;

				if ($article->thumbnail_image_type == "File")
					$selectedFileId = $article->thumbnail_image_file_id;

				if ($article->thumbnail_image_type == "Media Item")
					$selectedMediaItemId = $article->thumbnail_image_media_item_id;
			}
		}

		$data = [
			'title'                     => Fractal::lang('labels.selectThumbnailImage'),
			'defaultThumbnailImageType' => $defaultThumbnailImageType,
			'selectedFileId'            => $selectedFileId,
			'selectedMediaItemId'       => $selectedMediaItemId,
			'files'                     => ContentFile::where('type_id', 1)->orderBy('name')->get(),
			'mediaItems'                => MediaItem::where('file_type_id', 1)->orderBy('title')->get(),
		];

		return Fractal::modalView('select_thumbnail_image', $data);
	}

}