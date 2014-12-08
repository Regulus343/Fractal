<?php namespace Regulus\Fractal;

use \Fractal;

/*
|--------------------------------------------------------------------------
| Validation Rules
|--------------------------------------------------------------------------
|
| Custom validation rules for the CMS.
|
*/

use Illuminate\Support\Facades\Validator;

Validator::extend('lowercase_not_in', function($attribute, $value, $parameters)
{
	return !in_array(strtolower(trim((string) $value)), $parameters);
});

Validator::replacer('lowercase_not_in', function($message, $attribute, $rule, $parameters)
{
	return Fractal::lang('validation.lowercaseNotIn', ['attribute' => $attribute]);
});