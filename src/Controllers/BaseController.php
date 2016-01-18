<?php namespace Regulus\Fractal\Controllers;

use App\Http\Controllers\Controller;

use Fractal;

use Auth;
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

		if (!is_null($httpStatusCode))
		{
			return response($response, $httpStatusCode)->header('Content-Type', 'text/json');
		}

		return $response;
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