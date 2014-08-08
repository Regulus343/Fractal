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
			'home'     => 'Regulus\Fractal\Controllers\CoreController',
			'settings' => 'Regulus\Fractal\Controllers\SettingsController',
			'account'  => 'Regulus\Fractal\Controllers\AccountController',
			'activity' => 'Regulus\Fractal\Controllers\ActivityController',
		),
		'resource' => array(
			'menus'         => 'Regulus\Fractal\Controllers\MenusController',
			'pages'         => 'Regulus\Fractal\Controllers\PagesController',
			'files'         => 'Regulus\Fractal\Controllers\FilesController',
			'users'         => 'Regulus\Fractal\Controllers\UsersController',
			'user-roles'    => 'Regulus\Fractal\Controllers\UserRolesController',
			'blog/articles' => 'Regulus\Fractal\Controllers\Blogs\ArticlesController',
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
			'pages/add-content-area/{id?}'           => 'Regulus\Fractal\Controllers\PagesController@addContentArea',
			'pages/get-content-area/{id}'            => 'Regulus\Fractal\Controllers\PagesController@getContentArea',
			'pages/delete-content-area/{id}'         => 'Regulus\Fractal\Controllers\PagesController@deleteContentArea',
			'users/{id}/ban'                         => 'Regulus\Fractal\Controllers\UsersController@ban',
			'users/{id}/unban'                       => 'Regulus\Fractal\Controllers\UsersController@unban',
			'blog/articles/add-content-area/{id?}'   => 'Regulus\Fractal\Controllers\PagesController@addContentArea',
			'blog/articles/get-content-area/{id}'    => 'Regulus\Fractal\Controllers\PagesController@getContentArea',
			'blog/articles/delete-content-area/{id}' => 'Regulus\Fractal\Controllers\PagesController@deleteContentArea',
		),
		'post' => array(
			'menus/search'                          => 'Regulus\Fractal\Controllers\MenusController@search',
			'pages/search'                          => 'Regulus\Fractal\Controllers\PagesController@search',
			'pages/layout-tags'                     => 'Regulus\Fractal\Controllers\PagesController@layoutTags',
			'pages/render-markdown-content'         => 'Regulus\Fractal\Controllers\PagesController@renderMarkdownContent',
			'files/search'                          => 'Regulus\Fractal\Controllers\FilesController@search',
			'users/search'                          => 'Regulus\Fractal\Controllers\UsersController@search',
			'user-roles/search'                     => 'Regulus\Fractal\Controllers\UserRolesController@search',
			'blog/articles/search'                  => 'Regulus\Fractal\Controllers\Blogs\ArticlesController@search',
			'blog/articles/layout-tags'             => 'Regulus\Fractal\Controllers\Blogs\ArticlesController@layoutTags',
			'blog/articles/render-markdown-content' => 'Regulus\Fractal\Controllers\Blogs\ArticlesController@renderMarkdownContent',
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
	'pageMethod' => 'Regulus\Fractal\Controllers\PagesController@view',

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
	| Website Content Home Page for Root
	|--------------------------------------------------------------------------
	|
	| By default, the web root such as "http://website.com" will be routed to
	| the page will the slug "home" if it exists. Set this to false if you have
	| your own custom route mapped in your app. Since the app's routes file
	| will overwrite the routes mapped within Fractal, you should not even need
	| to adjust this, but it is here just in case.
	|
	*/
	'useHomePageForRoot' => true,

	/*
	|--------------------------------------------------------------------------
	| User Role No CMS Access URI
	|--------------------------------------------------------------------------
	|
	| The URI to redirect a user to if they log in successfully but they don't
	| have a user role assigned that grants access to the CMS. By default,
	| the setting is blank so the user will be redirected to the website's home
	| page. If you want to redirect to a non-CMS account page, you may set it
	| to something like "account" and set "userRoleNoCmsAccessLogOut" to false.
	|
	*/
	'userRoleNoCmsAccessUri' => '',

	/*
	|--------------------------------------------------------------------------
	| User Role No CMS Access Log Out
	|--------------------------------------------------------------------------
	|
	| Automatically log a user out if they attempt to access a CMS route but
	| don't have a user role assigned that grants access to the CMS. You may
	| set this to false if you would instead like to redirect a user to a
	| non-CMS account page using the "userRoleNoCmsAccessUri" setting above.
	|
	*/
	'userRoleNoCmsAccessLogOut' => true,

	/*
	|--------------------------------------------------------------------------
	| Logo
	|--------------------------------------------------------------------------
	|
	| The filename of the logo. Set this to false to use text instead of a
	| logo image. If no extension is given, PNG will be assumed.
	|
	*/
	'logo' => 'logo',

	/*
	|--------------------------------------------------------------------------
	| Logo in Fractal Assets Directory
	|--------------------------------------------------------------------------
	|
	| Set this to false if your logo lies in your regular images directory.
	| If true, the logo path will be "public/packages/regulus/fractal/images".
	|
	*/
	'logoFractal' => true,

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
	'authController' => 'Regulus\Fractal\Controllers\AuthController',

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
		'x'        => array('admin'),
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
	| Use Exterminator Package
	|--------------------------------------------------------------------------
	|
	| If your website uses the "Regulus/Exterminator" package for debugging,
	| set this to true to use it within Fractal.
	|
	*/
	'exterminator' => true,

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