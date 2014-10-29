<?php namespace Regulus\Fractal\Controllers;

use Fractal;

use \Auth;
use \Site;

class BaseController extends \BaseController {

	public function __construct()
	{
		Fractal::addTrailItem(Fractal::lang('labels.home'), '');
	}

}