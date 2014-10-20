<?php

/*
|--------------------------------------------------------------------------
| Helper Functions
|--------------------------------------------------------------------------
|
| A number of helper functions are available for various things.
|
*/

/**
 * Get a language item from language arrays.
 *
 * @param  string   $key
 * @param  array    $replace
 * @param  mixed    $locale
 * @return string
 */
function lang($key, array $replace = [], $locale = null)
{
	return \Illuminate\Support\Facades\Lang::get($key, $replace, $locale);
}

/**
 * Get a language item from language arrays and make it lowercase.
 *
 * @param  string   $key
 * @param  array    $replace
 * @param  mixed    $locale
 * @return string
 */
function lang_lower($key, array $replace = [], $locale = null)
{
	return strtolower(lang($key, $replace, $locale));
}

/**
 * Get a language item from language arrays and add "a" or "an" prefix.
 *
 * @param  string   $key
 * @param  array    $replace
 * @param  mixed    $locale
 * @return string
 */
function lang_a($key, array $replace = [], $locale = null)
{
	return \Regulus\TetraText\Facade::a(lang($key, $replace, $locale));
}

/**
 * Get a language item from language arrays, make it lowercase, and add "a" or "an" prefix.
 *
 * @param  string   $key
 * @param  array    $replace
 * @param  mixed    $locale
 * @return string
 */
function lang_lower_a($key, array $replace = [], $locale = null)
{
	return \Regulus\TetraText\Facade::a(lang_lower($key, $replace, $locale));
}

/**
 * Get a language item from language arrays and make it plural.
 *
 * @param  string   $key
 * @param  array    $replace
 * @param  mixed    $locale
 * @return string
 */
function lang_plural($key, array $replace = [], $locale = null)
{
	return \Illuminate\Support\Str::plural(lang($key, $replace, $locale));
}

/**
 * Get a language item from language arrays, make it lowercase, and make it plural.
 *
 * @param  string   $key
 * @param  array    $replace
 * @param  mixed    $locale
 * @return string
 */
function lang_lower_plural($key, array $replace = [], $locale = null)
{
	return \Illuminate\Support\Str::plural(lang_lower($key, $replace, $locale));
}