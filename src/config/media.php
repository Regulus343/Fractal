<?php

return [

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
	| will be mapped to "http://media.website.com". Set to null, false, or
	| blank to not use a subdomain.
	|
	*/

	'subdomain' => 'media',

	/*
	|--------------------------------------------------------------------------
	| Base URI
	|--------------------------------------------------------------------------
	|
	| The URI for the media. The default is null as the media is configured by
	| default to use a subdomain instead. If you would like to have a URL like
	| "http://website.com/media" instead, set this to "media" and set
	| "subdomain" to null.
	|
	*/

	'base_uri' => null,

	/*
	|--------------------------------------------------------------------------
	| Short Routes
	|--------------------------------------------------------------------------
	|
	| Whether to turn on short routes for media item URLs. If short routes is
	| turned on, media.website.com/example-item will work along with the
	| media.website.com/item/example-item and media.website.com/i/example-item
	| routes that are automatically set. The ID routes will work as well, such
	| as media.website.com/1. The example URLs are assuming default subdomain
	| and base URI settings.
	|
	*/

	'short_routes' => true,

	/*
	|--------------------------------------------------------------------------
	| Media View Controller
	|--------------------------------------------------------------------------
	|
	| The controller for viewing the media section. If you have your own custom
	| controller, point this setting to it instead of the default.
	|
	*/

	'view_controller' => 'Regulus\Fractal\Controllers\Media\ViewController',

	/*
	|--------------------------------------------------------------------------
	| Views Location
	|--------------------------------------------------------------------------
	|
	| The location of your media views. It is defaulted to "fractal::" to use
	| Fractal's built-in views, but you may point it towards a views directory
	| of your own for full view customization.
	|
	*/

	'views_location' => 'fractal::media.view',

];
