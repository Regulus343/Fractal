<?php namespace Regulus\Fractal\Controllers;

use App\Http\Controllers\Controller;

use Fractal;

use Auth;
use Form;
use Session;
use Site;

class BaseController extends Controller {

	public function __construct()
	{
		Fractal::addTrailItem(Fractal::trans('labels.home'), '');
	}

	/* API Response Functions */

	protected function response($type = 'Success', $message = null, $data = [], $httpStatusCode = null)
	{
		if (substr($message, 0, 9) == "messages.")
			$message = Fractal::trans($message);

		$response = [
			'type'    => $type,
			'message' => $message,
			'data'    => $data,
		];

		// if a message is set and URL redirection is going to occur, flash the message to the session
		if (!is_null($message))
		{
			if ((isset($data['url']) && !is_null($data['url'])) || (isset($data['uri']) && !is_null($data['uri'])))
				Session::flash('messages', [strtolower($type) => $message]);
		}

		if (is_null($httpStatusCode))
			$httpStatusCode = 200;

		return response($response, $httpStatusCode)->header('Content-Type', 'text/json');
	}

	protected function success($message = null, $data = [], $httpStatusCode = null)
	{
		return static::response('Success', $message, $data, $httpStatusCode);
	}

	protected function error($message = null, $data = [], $httpStatusCode = null)
	{
		if (is_null($message))
			$message = "messages.errors.general";

		if (!isset($data['errors']))
			$data['errors'] = Form::getErrors();

		return static::response('Error', $message, $data, $httpStatusCode);
	}

	protected function warning($message = null, $data = [], $httpStatusCode = null)
	{
		return static::response('Warning', $message, $data, $httpStatusCode);
	}

	protected function info($message = null, $data = [], $httpStatusCode = null)
	{
		return static::response('Info', $message, $data, $httpStatusCode);
	}

}