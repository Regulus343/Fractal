<?php namespace Regulus\Fractal\Controllers\Blogs;

use App\Http\Controllers\Controller;

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

class ViewController extends Controller {

	public function __construct()
	{
		Site::set('public', true);

		Site::setMulti(['section', 'title'], Fractal::trans('labels.blog'));

		Fractal::setViewsLocation(Config::get('fractal::blogs.viewsLocation'), true);

		Site::addTrailItems([
			Fractal::trans('labels.home') => Site::rootUrl(),
			Fractal::trans('labels.blog') => Fractal::blogUrl(),
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

	public function getP($page = 1)
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
				'error' => Fractal::trans('messages.errorNotFound', ['item' => Fractal::transLower('labels.article')])
			]);

		//allow article selection by ID for to allow shorter URLs
		if (is_numeric($slug))
		{
			$article = Article::where('id', $slug)->onlyPublished(true)->first();

			//if article is found by ID, redirect to URL with slug
			if (!empty($article))
				return Redirect::to($article->getUrl());
		}

		$article = Article::findBySlug($slug, true)->onlyPublished(true)->first();

		//if article is not found, check slug without dashes
		if (empty($article))
		{
			$article = Article::findByDashlessSlug($slug, true)->onlyPublished(true)->first();

			//if article is found by dashless slug, redirect to URL with proper slug
			if (!empty($article))
				return Redirect::to($article->getUrl());
		}

		if (empty($article) || (!Auth::is('admin') && !$article->isPublished()))
			return Redirect::to(Fractal::blogUrl())->with('messages', [
				'error' => Fractal::trans('messages.errorNotFound', ['item' => Fractal::transLower('labels.article')])
			]);

		Site::setMulti(['subSection', 'title', 'articleTitle'], $article->getTitle());
		Site::set('contentColumnWidth', 9);
		Site::set('pageIdentifier', 'article/'.$article->slug);

		Site::set('contentUrl', $article->getUrl());
		Site::set('contentImage', $article->getThumbnailImageUrl());
		Site::set('contentDescription', strip_tags($article->getRenderedContent(['previewOnly' => true, 'addReadMoreButton' => false])));

		Site::addTrailItem(strip_tags($article->getTitle()), $article->getUrl());

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
				$messages['info'] = Fractal::trans('messages.notPublishedUntil', [
					'item'     => strtolower(Fractal::trans('labels.page')),
					'dateTime' => $article->getPublishedDateTime(),
				]);
			else
				$messages['info'] = Fractal::trans('messages.notPublished', ['item' => Fractal::trans('labels.article')]);
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
				'error' => Fractal::trans('messages.errorNotFound', ['item' => Fractal::transLower('labels.category')])
			]);

		$category = Category::findBySlug($slug);
		if (empty($category))
			return Redirect::to(Fractal::blogUrl())->with('messages', [
				'error' => Fractal::trans('messages.errorNotFound', ['item' => Fractal::transLower('labels.category')])
			]);

		Site::setTitle(Fractal::trans('labels.blog').': '.$category->name);
		Site::set('articleList', true);
		Site::set('contentColumnWidth', 9);

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

		$messages = [];

		if (!empty($articleIds))
		{
			$articles = Article::whereIn('id', $articleIds)
				->orderBy('published_at', 'desc');

			if (Auth::isNot('admin'))
				$articles->onlyPublished();

			$articles = $articles->get();
		} else {
			$articles = [];

			$messages['error'] = Fractal::trans('messages.errorNoItems', [
				'item'  => Fractal::transLower('labels.category'),
				'items' => Fractal::transLower('labels.articles'),
			]);
		}

		Site::addTrailItem($category->name, Request::url());

		return View::make(Fractal::view('list'))
			->with('articles', $articles)
			->with('category', $category)
			->with('messages', $messages);
	}

	public function getC($slug = null) {
		return $this->getCategory($slug);
	}

}