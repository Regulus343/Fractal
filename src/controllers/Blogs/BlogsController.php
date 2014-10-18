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

class BlogsController extends BaseController {

	public function __construct()
	{
		parent::__construct();

		Site::set('section', 'Content');
		$subSection = "Blogs";
		Site::set('subSection', $subSection);
		Site::set('title', 'Blogs');

		//set content type and views location
		Fractal::setContentType('blog-article', true);

		Fractal::setViewsLocation('blogs');
	}

	public function index()
	{
		return Redirect::to(Fractal::uri('blogs/articles'));
	}

}