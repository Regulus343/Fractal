<?php namespace Regulus\Fractal\Controllers\Media;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

use Fractal;

use Regulus\Fractal\Models\MediaItem;
use Regulus\Fractal\Models\MediaType;
use Regulus\Fractal\Models\FileType;

use Regulus\ActivityLog\Activity;
use \Auth;
use \Form;
use \Format;
use \Site;

class MediaController extends BaseController {

	public function __construct()
	{
		parent::__construct();

		Site::set('section', 'Content');
		$subSection = "Media";
		Site::setMulti(array('subSection', 'title'), $subSection);

		//set content type and views location
		Fractal::setContentType('media-item', true);

		Fractal::setViewsLocation('media.items');
	}

	public function index()
	{
		return Redirect::to(Fractal::uri('media/items'));
	}

}