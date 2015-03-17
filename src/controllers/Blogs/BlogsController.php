<?php namespace Regulus\Fractal\Controllers\Blogs;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

use Fractal;

use Regulus\Fractal\Models\Blog\Article;

use Regulus\ActivityLog\Models\Activity;
use Auth;
use Form;
use Format;
use Site;

class BlogsController extends BaseController {

	public function __construct()
	{
		parent::__construct();

		Fractal::setControllerPath(get_class());

		Site::setMulti(['section', 'subSection'], 'Blogs');
		Site::setTitle(Fractal::transChoice('labels.blog', 2));

		Fractal::addTrailItem('Blogs', Fractal::getControllerPath());
	}

	public function index()
	{
		return Redirect::to(Fractal::uri('articles', true));
	}

}