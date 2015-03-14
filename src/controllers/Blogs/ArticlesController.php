<?php namespace Regulus\Fractal\Controllers\Blogs;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

use Fractal;

use Regulus\Fractal\Models\Blogs\Article;
use Regulus\Fractal\Models\Blogs\ContentArea;
use Regulus\Fractal\Models\Content\LayoutTemplate;
use Regulus\Fractal\Models\Content\File as ContentFile;
use Regulus\Fractal\Models\Media\Item as MediaItem;

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
		Site::setTitle(Fractal::transChoice('labels.blog_article', 2));

		// set content type and views location
		Fractal::setContentType('blog-article');

		Fractal::setViewsLocation('blogs.articles');

		Fractal::addTrailItem(Fractal::transChoice('labels.article', 2), Fractal::getControllerPath());

		Site::set('defaultSorting', ['order' => 'desc']);
	}

	public function index()
	{
		$data     = Fractal::setupPagination();
		$articles = Article::getSearchResults($data);

		Fractal::setContentForPagination($articles);

		$data     = Fractal::setPaginationMessage(true);
		$messages = Fractal::getPaginationMessageArray();

		if (!count($articles))
			$articles = Article::orderBy($data['sortField'], $data['sortOrder'])->paginate($data['itemsPerPage']);

		Fractal::addButton([
			'label' => Fractal::trans('labels.create_item', ['item' => Fractal::transChoice('labels.article')]),
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
		$articles = Article::getSearchResults($data);

		Fractal::setContentForPagination($articles);

		$data = Fractal::setPaginationMessage();

		if (!count($articles))
			$data['content'] = Article::orderBy($data['sortField'], $data['sortOrder'])->paginate($data['itemsPerPage']);

		$data['result']['pages']     = Fractal::getLastPage();
		$data['result']['tableBody'] = Fractal::createTable($data['content'], true);

		return $data['result'];
	}

	public function create()
	{
		Site::setTitle(Fractal::trans('labels.create_item', ['item' => Fractal::transChoice('labels.article')]));
		Site::set('wysiwyg', true);

		Article::setDefaultsForNew();

		// load auto-saved content
		$autoSavedContent = \Auth::user()->getState('autoSavedContent');
		if (!is_null($autoSavedContent) && isset($autoSavedContent->blogArticle))
			Form::setDefaults($autoSavedContent->blogArticle, null, 'content_areas.1');

		Form::setErrors();

		$layoutTagOptions = $this->getLayoutTagOptions();

		Fractal::addButton([
			'label' => Fractal::trans('labels.return_to_items_list', ['item' => Fractal::transChoice('labels.article')]),
			'icon'  => 'glyphicon glyphicon-list',
			'uri'   => Fractal::uri('', true),
		]);

		Fractal::addTrailItem(Fractal::trans('labels.create'), Request::url());

		return View::make(Fractal::view('form'))
			->with('layoutTagOptions', $layoutTagOptions);
	}

	public function store()
	{
		Form::setValidationRules(Article::validationRules());

		$messages = [];
		if (Form::validated()) {
			$messages['success'] = Fractal::trans('messages.success.created', ['item' => Fractal::transLowerA('labels.article')]);

			$input            = Input::all();
			$input['user_id'] = Auth::user()->id;

			$article = Article::createNew($input);

			Activity::log([
				'contentId'   => $article->id,
				'contentType' => 'Article',
				'action'      => 'Create',
				'description' => 'Created an Article',
				'details'     => 'Title: '.$article->title,
			]);

			return Redirect::to(Fractal::uri('', true))
				->with('messages', $messages);
		} else {
			$messages['error'] = Fractal::trans('messages.errors.general');
		}

		return Redirect::to(Fractal::uri('create', true))
			->with('messages', $messages)
			->with('errors', Form::getErrors())
			->withInput();
	}

	public function edit($slug)
	{
		$article = Article::findBySlug($slug);
		if (empty($article))
			return Redirect::to(Fractal::uri('pages'))->with('messages', [
				'error' => Fractal::trans('messages.errors.not_found', ['item' => Fractal::transLower('labels.article')])
			]);

		Site::setTitle($article->getTitle().' ('.Fractal::transChoice('labels.article').')');
		Site::setHeading(Fractal::trans('labels.update_article').': <strong>'.$article->getTitle().'</strong>');
		Site::set('wysiwyg', true);

		$article->setDefaults([
			'contentAreas' => true,
			'categories'   => 'category_id',
		]);
		Form::setErrors();

		$layoutTagOptions = $this->getLayoutTagOptions($article->getLayoutTags());

		Fractal::addButtons([
			[
				'label' => Fractal::trans('labels.return_to_articles_list'),
				'icon'  => 'glyphicon glyphicon-list',
				'uri'   => Fractal::uri('', true),

			],[
				'label' => Fractal::trans('labels.view_article'),
				'icon'  => 'glyphicon glyphicon-file',
				'url'   => $article->getUrl(),
			]
		]);

		Fractal::addTrailItem(Fractal::trans('labels.update'), Request::url());

		return View::make(Fractal::view('form'))
			->with('update', true)
			->with('id', $article->id)
			->with('article', $article)
			->with('articleUrl', $article->getUrl())
			->with('layoutTagOptions', $layoutTagOptions);
	}

	public function update($slug)
	{
		$article = Article::findBySlug($slug);
		if (empty($article))
			return Redirect::to(Fractal::uri('pages'))->with('messages', [
				'error' => Fractal::trans('messages.errors.not_found', ['item' => Fractal::transLower('labels.article')])
			]);

		$article->setValidationRules();

		$messages = [];
		if (Form::validated()) {
			$messages['success'] = Fractal::trans('messages.success.updated', ['item' => Fractal::transLowerA('labels.article')]);

			$article->saveData();

			Activity::log([
				'contentId'   => $article->id,
				'contentType' => 'Article',
				'action'      => 'Update',
				'description' => 'Updated an Article',
				'details'     => 'Title: '.$article->title,
				'updated'     => true,
			]);
		} else {
			$messages['error'] = Fractal::trans('messages.errors.general');
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
			'message'    => Fractal::trans('messages.errors.general'),
		];

		$article = Article::find($id);
		if (empty($article))
			return $result;

		Activity::log([
			'contentId'   => $article->id,
			'contentType' => 'Article',
			'action'      => 'Delete',
			'description' => 'Deleted an Article',
			'details'     => 'Title: '.$article->title,
		]);

		$result['resultType'] = "Success";
		$result['message']    = Fractal::trans('messages.success.deleted', ['item' => '<strong>'.$article->getTitle().'</strong>']);

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
		return Fractal::renderMarkdownContent(Input::get('content'));
	}

	public function addContentArea($id = null)
	{
		$data = [
			'title'        => Fractal::trans('labels.addContentArea'),
			'articleId'    => $id,
			'contentAreas' => ContentArea::orderBy('title')->get(),
		];

		return Fractal::modalView('add_content_area', $data);
	}

	public function getContentArea($id = null)
	{
		return ContentArea::find($id)->toJson();
	}

	public function deleteContentArea($id)
	{
		$contentArea = ContentArea::find($id);
		if ($contentArea) {
			if (!$contentArea->articles()->count()) {
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
			$article = Article::find($id);

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
			'title'                     => Fractal::trans('labels.selectThumbnailImage'),
			'defaultThumbnailImageType' => $defaultThumbnailImageType,
			'selectedFileId'            => $selectedFileId,
			'selectedMediaItemId'       => $selectedMediaItemId,
			'files'                     => ContentFile::where('type_id', 1)->orderBy('name')->get(),
			'mediaItems'                => MediaItem::where('file_type_id', 1)->orderBy('title')->get(),
		];

		return Fractal::modalView('select_thumbnail_image', $data);
	}

}