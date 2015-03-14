<?php namespace Regulus\Fractal\Controllers;

use App\Http\Controllers\Controller;

use Fractal;

use \Auth;
use \Site;

class BaseController extends Controller {

	public function __construct()
	{
		Fractal::addTrailItem(Fractal::trans('labels.home'), '');
	}

}