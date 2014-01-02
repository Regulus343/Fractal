<?php

return array(

	/*
	|--------------------------------------------------------------------------
	| Base URI
	|--------------------------------------------------------------------------
	|
	| The URI for Fractal. The default is "admin" but you might perhaps
	| wish to use "cms" or something else instead.
	|
	*/
	'baseUri' => 'admin',

	/*
	|--------------------------------------------------------------------------
	| Controllers
	|--------------------------------------------------------------------------
	|
	| The controllers that are used in the CMS. This array includes "standard"
	| and "resource" controllers. You may edit the "controllers" array to add
	| your own custom controllers which allows Fractal to be easily tailored
	| to any project. You may even redefine core controllers such as "pages".
	|
	*/
	'controllers' => array(
		'standard' => array(
			'home'       => 'Regulus\Fractal\CoreController',
			'settings'   => 'Regulus\Fractal\SettingsController',
			'account'    => 'Regulus\Fractal\AccountController',
			'activity'   => 'Regulus\Fractal\ActivityController',
		),
		'resource' => array(
			'menus'      => 'Regulus\Fractal\MenusController',
			'pages'      => 'Regulus\Fractal\PagesController',
			'files'      => 'Regulus\Fractal\FilesController',
			'users'      => 'Regulus\Fractal\UsersController',
			'user-roles' => 'Regulus\Fractal\UserRolesController',
		),
	),

	/*
	|--------------------------------------------------------------------------
	| Controller Methods
	|--------------------------------------------------------------------------
	|
	| The additional methods that are not automatically routed by the
	| controller setup. They are separated into a "get" array and a "post"
	| array.
	|
	*/
	'controllerMethods' => array(
		'get'  => array(
			'users/ban/{id}'    => 'Regulus\Fractal\UsersController@ban',
			'users/unban/{id}'  => 'Regulus\Fractal\UsersController@unban',
		),
		'post' => array(
			'files/search'      => 'Regulus\Fractal\FilesController@search',
			'users/search'      => 'Regulus\Fractal\UsersController@search',
		),
	),

	/*
	|--------------------------------------------------------------------------
	| Layout
	|--------------------------------------------------------------------------
	|
	| The location of your forum view layout. It is defaulted to
	| "fractal::layouts.master" to use Fractal's built-in view layout,
	| but you may point it towards a directory of your own for full layout
	| customization.
	|
	*/
	'layout' => 'fractal::layouts.master',

	/*
	|--------------------------------------------------------------------------
	| Views Location
	|--------------------------------------------------------------------------
	|
	| The location of your forum views. It is defaulted to "fractal::" to
	| use Fractal's built-in views, but you may point it towards a views
	| directory of your own for full view customization.
	|
	*/
	'viewsLocation' => 'fractal::',

	/*
	|--------------------------------------------------------------------------
	| Website Content Page URI
	|--------------------------------------------------------------------------
	|
	| The URI for content pages on the website. By default, it is left blank to
	| define the page URI at the root of the website. For example "" would give
	| you a URL like "http://localhost/home" and "page" would give you
	| "http://localhost/page/home".
	|
	*/
	'pageUri' => '',

	/*
	|--------------------------------------------------------------------------
	| Website Content Page Method
	|--------------------------------------------------------------------------
	|
	| The URI for content pages on the website. By default, it is left blank to
	| define the page URI at the root of the website. For example "" would give
	| you a URL like "http://localhost/home" and "page" would give you
	| "http://localhost/page/home".
	|
	*/
	'pageMethod' => 'Regulus\Fractal\PagesController@view',

	/*
	|--------------------------------------------------------------------------
	| Website Content Page View
	|--------------------------------------------------------------------------
	|
	| If the Page Method setting above is kept as the default, you may
	| adjust the view here to load a custom view while keeping the default
	| controller method. This allows you to easily plug the content pages
	| system into your website by creating a view and pointing this config
	| setting to it.
	|
	*/
	'pageView' => 'fractal::pages.view',

	/*
	|--------------------------------------------------------------------------
	| Authorization Class
	|--------------------------------------------------------------------------
	|
	| The name of your authorization class including the namespace and aF
	| leading backslash. This variable along with the "authMethod" variables
	| allow Fractal's built-in views to remain authorization class agnostic.
	| The default is "\Illuminate\Support\Facades\Auth" which is Laravel 4's
	| native authorization class.
	|
	*/
	'authClass' => '\Illuminate\Support\Facades\Auth',

	/*
	|--------------------------------------------------------------------------
	| Authorization Controller
	|--------------------------------------------------------------------------
	|
	| The name of your authorization controller.
	|
	*/
	'authController' => 'Regulus\Fractal\AuthController',

	/*
	|--------------------------------------------------------------------------
	| Authorization Method - Authentication Check
	|--------------------------------------------------------------------------
	|
	| The method in your authorization class that checks if user is logged in.
	| The default is "check()" which, along with the default auth class above,
	| selects Laravel 4's native authentication method.
	|
	*/
	'authMethodActiveCheck' => 'check()',

	/*
	|--------------------------------------------------------------------------
	| Authorization Method - User
	|--------------------------------------------------------------------------
	|
	| The method for getting the active user.
	|
	*/
	'authMethodActiveUser' => 'user()',

	/*
	|--------------------------------------------------------------------------
	| Authorization Method - User ID
	|--------------------------------------------------------------------------
	|
	| The attribute for getting the active user ID which is used in conjunction
	| with the user method above. By default, they get "user()->id" together.
	|
	*/
	'authMethodActiveUserID' => 'id',

	/*
	|--------------------------------------------------------------------------
	| Authorization Method - Admin Check
	|--------------------------------------------------------------------------
	|
	| The method in your authorization class that checks if the logged in user
	| is an administrator. Set this to false if you do not have a method of
	| identifying an admin.
	|
	*/
	'authMethodAdminCheck' => false,

	/*
	|--------------------------------------------------------------------------
	| Authorization - Admin Role
	|--------------------------------------------------------------------------
	|
	| The name of the admin role.
	|
	*/
	'authMethodAdminRole' => 'admin',

	/*
	|--------------------------------------------------------------------------
	| Authorization - Filters
	|--------------------------------------------------------------------------
	|
	| You may apply different filters to different URI routes to manage
	| role authorization for the whole CMS.
	|
	*/
	'authFilters' => array(
		'X'        => array('admin'),
		'menus'    => array('admin'),
		'pages'    => array('admin'),
		'files'    => array('admin'),
		'users'    => array('admin'),
		'settings' => array('admin'),
	),

	/*
	|--------------------------------------------------------------------------
	| Authorization - Filters from Menu Items
	|--------------------------------------------------------------------------
	|
	| Turning on Menu Item Authorization Filters will add an authorization
	| filter editor to the menu item form allowing you to customize
	| authorization within the CMS. Setting the Developers Only option will
	| hide this option from the forms unless the "developer" session is set.
	|
	*/
	'enableMenuItemAuthFilters'        => true,
	'menuItemAuthFiltersDeveloperOnly' => true,

	/*
	|--------------------------------------------------------------------------
	| Load jQuery
	|--------------------------------------------------------------------------
	|
	| Whether or not to have Fractal automatically load jQuery.
	| Turn this off if your website already loads jQuery.
	|
	*/
	'loadJquery'   => true,
	'loadJqueryUi' => true,

	/*
	|--------------------------------------------------------------------------
	| Load Bootstrap
	|--------------------------------------------------------------------------
	|
	| Whether or not to have Fractal automatically load Twitter Bootsrap.
	| If set to false, Fractal will assume you are already loading
	| Bootstrap CSS and JS files. If true, Fractal will attempt to load
	| "bootstrap.css" and "bootstrap.min.js".
	|
	*/
	'loadBootstrap' => true,

	/*
	|--------------------------------------------------------------------------
	| Load Boxy
	|--------------------------------------------------------------------------
	|
	| By default, Fractal makes use of the lightweight javascript
	| library Boxy for modal windows like content deleting confirmation.
	| You may turn off Boxy if you intend to use another modal window script.
	|
	*/
	'loadBoxy' => true,

	/*
	|--------------------------------------------------------------------------
	| Date Format
	|--------------------------------------------------------------------------
	|
	| The default date formats.
	|
	*/
	'dateFormat'     => 'F j, Y',
	'dateTimeFormat' => 'F j, Y \a\t g:ia',

	/*
	|--------------------------------------------------------------------------
	| Migrated
	|--------------------------------------------------------------------------
	|
	| Set this to false if you registered the service provider and do not
	| currently have the database tables migrated to prevent an error from
	| occurring when you run the DB migrations.
	|
	*/
	'migrated' => true,

	/*
	|--------------------------------------------------------------------------
	| Workbench
	|--------------------------------------------------------------------------
	|
	| Set this to true if the package is being run from Laravel's Workbench.
	|
	*/
	'workbench' => false,

	/*
	|--------------------------------------------------------------------------
	| Display Version
	|--------------------------------------------------------------------------
	|
	| Set this to false if you would prefer not to show Fractal's version
	| number in the footer of the supplied layout.
	|
	*/
	'displayVersion' => true,

);