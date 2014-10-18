<?php namespace Regulus\Fractal\Controllers\Blogs;

use Fractal;

use \Auth;
use \Site;

class BaseController extends \Regulus\Fractal\Controllers\BaseController {

	public function __construct()
	{
		parent::__construct();

		Fractal::addTrailItem('Blogs', 'blogs');
	}

}