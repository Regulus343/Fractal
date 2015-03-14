<?php

/*
|--------------------------------------------------------------------------
| Helper Functions
|--------------------------------------------------------------------------
|
| A number of helper functions are available for various things.
|
*/

if ( ! function_exists('trans'))
{
	/**
	 * Get a language item from language arrays.
	 *
	 * @param  string   $key
	 * @param  array    $replace
	 * @param  mixed    $locale
	 * @return string
	 */
	function trans($key, array $replace = [], $locale = null)
	{
		return \Illuminate\Support\Facades\Lang::get($key, $replace, $locale);
	}
}

if ( ! function_exists('trans_lower'))
{
	/**
	 * Get a language item from language arrays and make it lowercase.
	 *
	 * @param  string   $key
	 * @param  array    $replace
	 * @param  mixed    $locale
	 * @return string
	 */
	function trans_lower($key, array $replace = [], $locale = null)
	{
		return strtolower(trans($key, $replace, $locale));
	}
}

if ( ! function_exists('trans_a'))
{
	/**
	 * Get a language item from language arrays and add "a" or "an" prefix.
	 *
	 * @param  string   $key
	 * @param  array    $replace
	 * @param  mixed    $locale
	 * @return string
	 */
	function trans_a($key, array $replace = [], $locale = null)
	{
		return \Regulus\TetraText\Facade::a(trans($key, $replace, $locale));
	}
}

if ( ! function_exists('trans_lower_a'))
{
	/**
	 * Get a language item from language arrays, make it lowercase, and add "a" or "an" prefix.
	 *
	 * @param  string   $key
	 * @param  array    $replace
	 * @param  mixed    $locale
	 * @return string
	 */
	function trans_lower_a($key, array $replace = [], $locale = null)
	{
		return \Regulus\TetraText\Facade::a(lang_lower($key, $replace, $locale));
	}
}

if ( ! function_exists('trans_plural'))
{
	/**
	 * Get a language item from language arrays and make it plural.
	 *
	 * @param  string   $key
	 * @param  array    $replace
	 * @param  mixed    $locale
	 * @return string
	 */
	function trans_plural($key, array $replace = [], $locale = null)
	{
		return \Illuminate\Support\Str::plural(trans($key, $replace, $locale));
	}
}

if ( ! function_exists('trans_plural_lower'))
{
	/**
	 * Get a language item from language arrays, make it lowercase, and make it plural.
	 *
	 * @param  string   $key
	 * @param  array    $replace
	 * @param  mixed    $locale
	 * @return string
	 */
	function trans_plural_lower($key, array $replace = [], $locale = null)
	{
		return \Illuminate\Support\Str::plural(lang_lower($key, $replace, $locale));
	}
}

if ( ! function_exists('trans_choice_lower'))
{
	/**
	 * Get a language item according to an integer value and make it lowercase.
	 *
	 * @param  string  $key
	 * @param  int     $number
	 * @param  array   $replace
	 * @param  string  $locale
	 * @return string
	 */
	function trans_choice_lower($key, $number = 1, array $replace = [], $locale = null)
	{
		return strtolower(trans_choice($key, $number, $replace, $locale));
	}
}

if ( ! function_exists('trans_choice_lower_a'))
{
	/**
	 * Get a language item according to an integer value and make it lowercase.
	 *
	 * @param  string  $key
	 * @param  int     $number
	 * @param  array   $replace
	 * @param  string  $locale
	 * @return string
	 */
	function trans_choice_lower_a($key, $number = 1, array $replace = [], $locale = null)
	{
		return \Regulus\TetraText\Facade::a(strtolower(trans_choice($key, $number, $replace, $locale)));
	}
}
