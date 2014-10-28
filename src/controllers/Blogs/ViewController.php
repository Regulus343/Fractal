<?php namespace Regulus\Fractal\Controllers\Blogs;

use \BaseController;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
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

	public function getIndex()
	{
		Site::set('articleList', true);

		$articles = BlogArticle::orderBy('published_at', 'desc');

		if (Auth::isNot('admin'))
			$articles->onlyPublished();

		$articles = $articles->get();

		return View::make(Fractal::view('home'))
			->with('articles', $articles);
	}

	public function getArticle($slug)
	{
		$article = BlogArticle::where('slug', $slug);

		if (Auth::isNot('admin'))
			$article->onlyPublished();

		$article = $article->first();

		if (empty($article))
			return Redirect::to('');

		Site::setMulti(['section', 'subSection', 'title', 'articleTitle'], $article->title);

		Site::addTrailItem($article->title, $article->getUrl());

		$article->logView();

		$articles = BlogArticle::orderBy('published_at', 'desc');

		if (Auth::isNot('admin'))
			$articles->onlyPublished();

		$articles = $articles->get();

		$messages = [];
		if (!$article->isPublished()) {
			if ($article->isPublishedFuture())
				$messages['info'] = Lang::get('fractal::messages.notPublishedUntil', [
					'item'     => strtolower(Lang::get('fractal::labels.page')),
					'dateTime' => $article->getPublishedDateTime(),
				]);
			else
				$messages['info'] = Lang::get('fractal::messages.notPublished', ['item' => Lang::get('fractal::labels.article')]);
		}

		return View::make(Fractal::view('article'))
			->with('article', $article)
			->with('articles', $articles)
			->with('messages', $messages);
	}

	public function getA($slug) {
		return $this->getArticle($slug);
	}

}