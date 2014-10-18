<?php namespace Regulus\Fractal\Controllers\Blogs;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

use Fractal;

use Regulus\Fractal\Models\BlogArticle;
use Regulus\Fractal\Models\BlogContentArea;
use Regulus\Fractal\Models\ContentLayoutTemplate;

use Regulus\ActivityLog\Activity;
use \Auth;
use \Form;
use \Format;
use \Site;

class ArticlesController extends BaseController {

	public function __construct()
	{
		parent::__construct();

		Site::set('section', 'Content');
		$subSection = "Blogs";
		Site::set('subSection', $subSection);
		Site::set('title', 'Blog Articles');

		//set content type and views location
		Fractal::setContentType('blog-article', true);

		Fractal::setViewsLocation('blogs.articles');

		Fractal::addTrailItem('Articles', 'blogs/articles');
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
			'label' => Lang::get('fractal::labels.createArticle'),
			'icon'  => 'glyphicon glyphicon-file',
			'uri'   => 'blogs/articles/create',
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
		Site::set('title', 'Create Article');
		Site::set('wysiwyg', true);

		BlogArticle::setDefaultsForNew();
		Form::setErrors();

		$layoutTagOptions = $this->getLayoutTagOptions();

		Fractal::addButton([
			'label' => Lang::get('fractal::labels.returnToArticlesList'),
			'icon'  => 'glyphicon glyphicon-list',
			'uri'   => 'blogs/articles',
		]);

		return View::make(Fractal::view('form'))
			->with('layoutTagOptions', $layoutTagOptions);
	}

	public function store()
	{
		Form::setValidationRules(BlogArticle::validationRules());

		$messages = array();
		if (Form::validated()) {
			$messages['success'] = Lang::get('fractal::messages.successCreated', array('item' => Format::a('article')));

			$input = Input::all();
			$input['user_id'] = Auth::user()->id;

			$article = BlogArticle::createNew($input);

			//re-export menus to config array in case published status for article has changed
			Fractal::exportMenus();

			Activity::log(array(
				'contentId'   => $article->id,
				'contentType' => 'BlogArticle',
				'action'      => 'Create',
				'description' => 'Created an Article',
				'details'     => 'Title: '.$article->title,
			));

			return Redirect::to(Fractal::uri('blog/articles/'.$article->slug.'/edit'))
				->with('messages', $messages);
		} else {
			$messages['error'] = Lang::get('fractal::messages.errorGeneral');
		}

		return Redirect::to(Fractal::uri('blog/articles/create'))
			->with('messages', $messages)
			->with('errors', Form::getErrors())
			->withInput();
	}

	public function edit($slug)
	{
		$article = BlogArticle::findBySlug($slug);
		if (empty($article))
			return Redirect::to(Fractal::uri('pages'))
				->with('messages', array('error' => Lang::get('fractal::messages.errorNotFound', array('item' => 'article'))));

		Site::set('title', $article->title.' (Article)');
		Site::set('titleHeading', 'Update Article: <strong>'.Format::entities($article->title).'</strong>');
		Site::set('wysiwyg', true);

		$article->setDefaults(array('contentAreas'));
		Form::setErrors();

		$layoutTagOptions = $this->getLayoutTagOptions($article->getLayoutTags());

		Fractal::addButtons([
			[
				'label' => Lang::get('fractal::labels.returnToArticlesList'),
				'icon'  => 'glyphicon glyphicon-list',
				'uri'   => 'blogs/articles',
			],[
				'label' => Lang::get('fractal::labels.viewArticle'),
				'icon'  => 'glyphicon glyphicon-file',
				'url'   => $article->getUrl(),
			]
		]);

		return View::make(Fractal::view('form'))
			->with('update', true)
			->with('id', $article->id)
			->with('articleUrl', $article->getUrl())
			->with('layoutTagOptions', $layoutTagOptions);
	}

	public function update($slug)
	{
		$article = BlogArticle::findBySlug($slug);
		if (empty($article))
			return Redirect::to(Fractal::uri('pages'))
				->with('messages', array('error' => Lang::get('fractal::messages.errorNotFound', array('item' => 'article'))));

		$article->setValidationRules();

		$messages = array();
		if (Form::validated()) {
			$messages['success'] = Lang::get('fractal::messages.successUpdated', array('item' => Format::a('article')));

			$article->saveData();

			Activity::log(array(
				'contentId'   => $article->id,
				'contentType' => 'BlogArticle',
				'action'      => 'Update',
				'description' => 'Updated an Article',
				'details'     => 'Title: '.$article->title,
				'updated'     => true,
			));

			return Redirect::to(Fractal::uri('blog/articles/'.$slug.'/edit'))
				->with('messages', $messages);
		} else {
			$messages['error'] = Lang::get('fractal::messages.errorGeneral');

			return Redirect::to(Fractal::uri('blog/articles/'.$slug.'/edit'))
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

		$article = BlogArticle::find($id);
		if (empty($article))
			return $result;

		Activity::log(array(
			'contentId'   => $article->id,
			'contentType' => 'BlogArticle',
			'action'      => 'Delete',
			'description' => 'Deleted an Article',
			'details'     => 'Title: '.$article->title,
		));

		$result['resultType'] = "Success";
		$result['message']    = Lang::get('fractal::messages.successDeleted', array('item' => '<strong>'.$article->title.'</strong>'));

		$article->contentAreas()->sync(array());
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

	private function getLayoutTagOptions($layoutTagOptions = array())
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
		$data = array(
			'title'        => Lang::get('fractal::labels.addContentArea'),
			'articleId'    => $id,
			'contentAreas' => BlogContentArea::orderBy('title')->get(),
		);

		return Fractal::modalView('add_content_area', $data);
	}

	public function getContentArea($id = false)
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

}