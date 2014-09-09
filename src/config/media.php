<?php

return array(

	/*
	|--------------------------------------------------------------------------
	| Enabled
	|--------------------------------------------------------------------------
	|
	| Whether or not the media is enabled.
	|
	*/
	'enabled' => true,

	/*
	|--------------------------------------------------------------------------
	| Subdomain
	|--------------------------------------------------------------------------
	|
	| The subdomain for the media URLs. The default is "media", so the media
	| will be mapped to "http://media.website.com". Set to false or blank to
	| not use a subdomain.
	|
	*/
	'subdomain' => 'media',

	/*
	|--------------------------------------------------------------------------
	| Base URI
	|--------------------------------------------------------------------------
	|
	| The URI for the media. The default is false as the media is configured by
	| default to use a subdomain instead. If you would like to have a URL like
	| "http://website.com/media" instead, set this to "media" and set
	| "subdomain" to false.
	|
	*/
	'baseUri' => false,

	/*
	|--------------------------------------------------------------------------
	| Media View Controller
	|--------------------------------------------------------------------------
	|
	| The controller for viewing the media section. If you have your own custom
	| controller, point this setting to it instead of the default.
	|
	*/
	'viewController' => 'Regulus\Fractal\Controllers\Media\ViewController',

);