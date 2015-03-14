<?php namespace Regulus\Fractal;

class Facade extends \Illuminate\Support\Facades\Facade {

	protected static function getFacadeAccessor() { return 'Regulus\Fractal\Fractal'; }

}