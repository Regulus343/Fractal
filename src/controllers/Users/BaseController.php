<?php namespace Regulus\Fractal\Controllers\Users;

use Fractal;

use \Auth;
use \Site;

class BaseController extends \Regulus\Fractal\Controllers\BaseController {

	public function __construct()
	{
		parent::__construct();

		Fractal::addTrailItem('Users', 'users');
	}

}