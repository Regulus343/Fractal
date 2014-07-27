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
use \Auth as Auth;
use \Site as Site;
use \Form as Form;
use \Format as Format;

class ViewController extends BaseController {

	public function __construct()
	{
		$section = "Blog";
		Site::setMulti(array('section', 'title'), $section);

		Site::set('menus', 'Front');

		Fractal::setViewsLocation('blogs.view');
	}

	public function getIndex()
	{
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

		Site::setMulti(array('section', 'subSection', 'title', 'articleTitle'), $article->title);

		$article->logView();

		$messages = array();
		if (!$article->isPublished()) {
			if ($article->isPublishedFuture())
				$messages['info'] = Lang::get('fractal::messages.notPublishedUntil', array(
					'item'     => strtolower(Lang::get('fractal::labels.page')),
					'dateTime' => $article->getPublishedDateTime(),
				));
			else
				$messages['info'] = Lang::get('fractal::messages.notPublished', array('item' => Lang::get('fractal::labels.page')));
		}

		return View::make(Fractal::view('article'))
			->with('article', $article)
			->with('messages', $messages);
	}

	public function getA($slug) {
		return $this->getArticle($slug);
	}

}