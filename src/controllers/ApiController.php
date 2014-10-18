<?php namespace Regulus\Fractal\Controllers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;

use Fractal;

use \Auth;

use Regulus\Fractal\Models\User;
use Regulus\Fractal\Models\Role;

class ApiController extends BaseController {

	public function postSetUserState()
	{
		return (int) Auth::setState(Input::get('name'), Input::get('state'));
	}

	public function postRemoveUserState()
	{
		return (int) Auth::removeState(Input::get('name'), Input::get('state'));
	}

}