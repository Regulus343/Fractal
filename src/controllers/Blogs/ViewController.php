<?php namespace Regulus\Fractal\Controllers\Blogs;

use \BaseController;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

use Fractal;

use Regulus\Fractal\Models\Blogs\Article;
use Regulus\Fractal\Models\Blogs\Category;

use Regulus\ActivityLog\Activity;
use \Auth;
use \Form;
use \Format;
use \Site;

class ViewController extends BaseController {

	public function __construct()
	{
		Site::setMulti(['section', 'title'], Fractal::lang('labels.blog'));

		Fractal::setViewsLocation('blogs.view');

		Site::addTrailItems([
			Fractal::lang('labels.home') => Site::rootUrl(),
			Fractal::lang('labels.blog') => Fractal::blogUrl(),
		]);
	}

	/* Articles List */

	public function getIndex()
	{
		return $this->getArticles();
	}

	public function getPage($page = 1)
	{
		return $this->getArticles($page);
	}

	public function getArticles($page = 1)
	{
		Site::set('articleList', true);
		Site::set('contentColumnWidth', 9);
		Site::set('paginationUrlSuffix', 'page');
		Site::set('currentPage', $page);

		DB::getPaginator()->setCurrentPage($page);

		$articles = Article::orderBy('published_at', 'desc');

		if (Auth::isNot('admin'))
			$articles->onlyPublished();

		$articles = $articles->paginate(Fractal::getSetting('Articles Listed Per Page', 10));

		Site::set('lastPage', $articles->getLastPage());

		return View::make(Fractal::view('list'))
			->with('articles', $articles);
	}

	/* Article */

	public function getArticle($slug = null)
	{
		if (is_null($slug))
			return Redirect::to(Fractal::blogUrl())->with('messages', [
				'error' => Fractal::lang('messages.errorNotFound', ['item' => Fractal::langLower('labels.article')])
			]);

		Site::set('contentColumnWidth', 9);

		//allow article selection by ID for to allow shorter URLs
		if (is_numeric($slug))
		{
			$article = Article::where('id', $slug);

			if (Auth::isNot('admin'))
				$article->onlyPublished();

			$article = $article->first();

			//if article is found by ID, redirect to URL with slug
			if (!empty($article))
				return Redirect::to($article->getUrl());
		}

		$article = Article::where('slug', $slug);

		if (Auth::isNot('admin'))
			$article->onlyPublished();

		$article = $article->first();

		if (empty($article))
			return Redirect::to(Fractal::blogUrl())->with('messages', [
				'error' => Fractal::lang('messages.errorNotFound', ['item' => Fractal::langLower('labels.article')])
			]);

		Site::setMulti(['subSection', 'title', 'articleTitle'], $article->title);

		Site::addTrailItem($article->title, $article->getUrl());

		$article->logView();

		$articles = Article::query()
			->where('id', '<=', ((int) $article->id + 3))
			->orderBy('published_at', 'desc')
			->take(8);

		if (Auth::isNot('admin'))
			$articles->onlyPublished();

		$articles = $articles->get();

		$messages = [];
		if (!$article->isPublished()) {
			if ($article->isPublishedFuture())
				$messages['info'] = Fractal::lang('messages.notPublishedUntil', [
					'item'     => strtolower(Fractal::lang('labels.page')),
					'dateTime' => $article->getPublishedDateTime(),
				]);
			else
				$messages['info'] = Fractal::lang('messages.notPublished', ['item' => Fractal::lang('labels.article')]);
		}

		return View::make(Fractal::view('article'))
			->with('article', $article)
			->with('articles', $articles)
			->with('messages', $messages);
	}

	public function getA($slug = null) {
		return $this->getArticle($slug);
	}

	/* Articles List for Category */

	public function getCategory($slug = null) {
		if (is_null($slug))
			return Redirect::to(Fractal::blogUrl())->with('messages', [
				'error' => Fractal::lang('messages.errorNotFound', ['item' => Fractal::langLower('labels.category')])
			]);

		Site::set('articleList', true);
		Site::set('contentColumnWidth', 9);

		$category = Category::findBySlug($slug);
		if (empty($category))
			return Redirect::to(Fractal::blogUrl())->with('messages', [
				'error' => Fractal::lang('messages.errorNotFound', ['item' => Fractal::langLower('labels.category')])
			]);

		$articles = Article::query()
			->select('blog_articles.id')
			->leftJoin('blog_article_categories', 'blog_articles.id', '=', 'blog_article_categories.article_id')
			->leftJoin('blog_categories', 'blog_article_categories.category_id', '=', 'blog_categories.id')
			->where('blog_categories.slug', $slug);

		if (Auth::isNot('admin'))
			$articles->onlyPublished();

		$articles = $articles->get();

		$articleIds = [];
		foreach ($articles as $article) {
			if (!in_array($article->id, $articleIds))
				$articleIds[] = $article->id;
		}

		if (empty($articleIds))
			return Redirect::to(Fractal::blogUrl())->with('messages', [
				'error' => Fractal::lang('messages.errorNoItems', [
					'item'  => Fractal::langLower('labels.category'),
					'items' => Fractal::langLower('labels.articles'),
				])
			]);

		$articles = Article::whereIn('id', $articleIds)
			->orderBy('published_at', 'desc');

		if (Auth::isNot('admin'))
			$articles->onlyPublished();

		$articles = $articles->get();

		Site::addTrailItem($category->name, Request::url());

		return View::make(Fractal::view('list'))
			->with('articles', $articles)
			->with('category', $category);
	}

	public function getC($slug = null) {
		return $this->getCategory($slug);
	}

}