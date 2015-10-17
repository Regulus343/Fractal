<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Subdomain
	|--------------------------------------------------------------------------
	|
	| The subdomain for the admin / CMS URLs. The default is "admin", so the
	| CMS will be mapped to "http://blog.website.com". Set to null, false, or
	| blank to not use a subdomain (in that case, you'll want to set base URI
	| below to something like "admin" or "cms").
	|
	*/

	'subdomain' => 'admin',

	/*
	|--------------------------------------------------------------------------
	| Base URI
	|--------------------------------------------------------------------------
	|
	| The base URI for Fractal. The default is "admin" but you might perhaps
	| wish to use "cms" or something else instead. If you're using a subdomain,
	| you should set this to null.
	|
	*/

	'base_uri' => null,

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

	'controllers' => [

		'standard' => [
			'home'              => 'Regulus\Fractal\Controllers\General\DashboardController:dashboard',
			'settings'          => 'Regulus\Fractal\Controllers\General\SettingsController:settings',
			'account'           => 'Regulus\Fractal\Controllers\Auth\AccountController:account',
			'users/activity'    => 'Regulus\Fractal\Controllers\Users\ActivityController:users.activity',
		],

		'resource' => [
			'menus'             => 'Regulus\Fractal\Controllers\Content\MenusController',
			'pages'             => 'Regulus\Fractal\Controllers\Content\PagesController',
			'files'             => 'Regulus\Fractal\Controllers\Content\FilesController',
			'layout-templates'  => 'Regulus\Fractal\Controllers\Content\LayoutTemplatesController',
			'users/roles'       => 'Regulus\Fractal\Controllers\Users\RolesController',
			'users/permissions' => 'Regulus\Fractal\Controllers\Users\PermissionsController',
			'users'             => 'Regulus\Fractal\Controllers\Users\UsersController',
			'media/items'       => 'Regulus\Fractal\Controllers\Media\ItemsController',
			'media/types'       => 'Regulus\Fractal\Controllers\Media\TypesController',
			'media/sets'        => 'Regulus\Fractal\Controllers\Media\SetsController',
			'media'             => 'Regulus\Fractal\Controllers\Media\MediaController',
			'blogs/articles'    => 'Regulus\Fractal\Controllers\Blogs\ArticlesController',
			'blogs/categories'  => 'Regulus\Fractal\Controllers\Blogs\CategoriesController',
			'blogs'             => 'Regulus\Fractal\Controllers\Blogs\BlogsController',
		],

	],

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

	'controller_methods' => [

		'get'  => [
			'pages/add-content-area/{id?}'                => 'Regulus\Fractal\Controllers\Content\PagesController@addContentArea',
			'pages/get-content-area/{id}'                 => 'Regulus\Fractal\Controllers\Content\PagesController@getContentArea',
			'pages/delete-content-area/{id}'              => 'Regulus\Fractal\Controllers\Content\PagesController@deleteContentArea',
			'users/{id}/ban'                              => 'Regulus\Fractal\Controllers\Users\UsersController@ban',
			'users/{id}/unban'                            => 'Regulus\Fractal\Controllers\Users\UsersController@unban',
			'media/items/get-types-for-file-type/{id?}'   => 'Regulus\Fractal\Controllers\Media\ItemsController@getTypesForFileType',
			'blogs/articles/add-content-area/{id?}'       => 'Regulus\Fractal\Controllers\Blogs\ArticlesController@addContentArea',
			'blogs/articles/get-content-area/{id}'        => 'Regulus\Fractal\Controllers\Blogs\ArticlesController@getContentArea',
			'blogs/articles/delete-content-area/{id}'     => 'Regulus\Fractal\Controllers\Blogs\ArticlesController@deleteContentArea',
			'blogs/articles/select-thumbnail-image/{id?}' => 'Regulus\Fractal\Controllers\Blogs\ArticlesController@selectThumbnailImage',
		],

		'post' => [
			'menus/search'                                => 'Regulus\Fractal\Controllers\Content\MenusController@search',
			'pages/search'                                => 'Regulus\Fractal\Controllers\Content\PagesController@search',
			'pages/layout-tags'                           => 'Regulus\Fractal\Controllers\Content\PagesController@layoutTags',
			'pages/render-markdown-content'               => 'Regulus\Fractal\Controllers\Content\PagesController@renderMarkdownContent',
			'files/search'                                => 'Regulus\Fractal\Controllers\Content\FilesController@search',
			'layout-templates/search'                     => 'Regulus\Fractal\Controllers\Content\LayoutTemplatesController@search',
			'users/search'                                => 'Regulus\Fractal\Controllers\Users\UsersController@search',
			'user-roles/search'                           => 'Regulus\Fractal\Controllers\Users\RolesController@search',
			'user-permissions/search'                     => 'Regulus\Fractal\Controllers\Users\PermissionsController@search',
			'media/items/search'                          => 'Regulus\Fractal\Controllers\Media\ItemsController@search',
			'media/types/search'                          => 'Regulus\Fractal\Controllers\Media\TypesController@search',
			'media/sets/search'                           => 'Regulus\Fractal\Controllers\Media\SetsController@search',
			'media/sets/add-item'                         => 'Regulus\Fractal\Controllers\Media\SetsController@addItem',
			'blogs/articles/search'                       => 'Regulus\Fractal\Controllers\Blogs\ArticlesController@search',
			'blogs/articles/layout-tags'                  => 'Regulus\Fractal\Controllers\Blogs\ArticlesController@layoutTags',
			'blogs/articles/render-markdown-content'      => 'Regulus\Fractal\Controllers\Blogs\ArticlesController@renderMarkdownContent',
		],

	],

	/*
	|--------------------------------------------------------------------------
	| Layouts
	|--------------------------------------------------------------------------
	|
	| The location of your view layout. It is defaulted to
	| "fractal::layouts.master" to use Fractal's built-in view layout,
	| but you may point it towards a directory of your own for full layout
	| customization.
	|
	*/

	'layout'        => 'fractal::layouts.master',
	'layout_public' => 'fractal::layouts.public',

	/*
	|--------------------------------------------------------------------------
	| Content Section
	|--------------------------------------------------------------------------
	|
	| The name of the content section used for views.
	|
	*/

	'content_section' => 'content',

	/*
	|--------------------------------------------------------------------------
	| Views Location
	|--------------------------------------------------------------------------
	|
	| The location of your views. It is defaulted to "fractal::" to use
	| Fractal's built-in views, but you may point it towards a views directory
	| of your own for full view customization.
	|
	*/

	'views_location' => 'fractal::',

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

	'page_uri' => '',

	/*
	|--------------------------------------------------------------------------
	| Website Content Page Method
	|--------------------------------------------------------------------------
	|
	| The method for viewing content pages. If you have your own custom method,
	| point this setting to it instead of the default.
	|
	*/

	'page_method' => 'Regulus\Fractal\Controllers\Content\PagesController@view',

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

	'page_view' => 'fractal::content.pages.view',

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

	'use_home_page_for_root' => true,

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

	'user_role_no_cms_access_uri' => '',

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

	'user_role_no_cms_access_log_out' => true,

	/*
	|--------------------------------------------------------------------------
	| Logo
	|--------------------------------------------------------------------------
	|
	| The path and filename of the logo. Set this to false to use text instead
	| of a logo image. If no extension is given, PNG will be assumed. You may
	| use a composer package path by preceding the file path with,
	| for example, "regulus/fractal::".
	|
	*/

	'logo' => 'regulus/fractal::logo',

	/*
	|--------------------------------------------------------------------------
	| Favicon
	|--------------------------------------------------------------------------
	|
	| The path and filename of the favicon. You may use a composer package path
	| by preceding the file path with, for example, "regulus/fractal::".
	|
	*/

	'favicon' => 'regulus/fractal::favicon.ico',

	/*
	|--------------------------------------------------------------------------
	| Placeholder Image
	|--------------------------------------------------------------------------
	|
	| The path and filename of the logo. If no extension is given, PNG will be
	| assumed. You may use a composer package path by preceding the file path
	| with, for example, "regulus/fractal::".
	|
	*/

	'placeholder_image' => 'regulus/fractal::image-not-available',

	/*
	|--------------------------------------------------------------------------
	| External Language Files
	|--------------------------------------------------------------------------
	|
	| Set this to true to use language files external to Fractal. You may copy
	| Fractal's language files to your application / website's language files
	| directory to use as a starting point.
	|
	*/

	'external_language' => false,

	/*
	|--------------------------------------------------------------------------
	| Authorization Controller
	|--------------------------------------------------------------------------
	|
	| The name of your authorization controller.
	|
	*/

	'auth_controller' => 'Regulus\Fractal\Controllers\Auth\AuthController',

	/*
	|--------------------------------------------------------------------------
	| Authorization - Admin Role
	|--------------------------------------------------------------------------
	|
	| The name of the admin role.
	|
	*/

	'auth_method_admin_role' => 'admin',

	/*
	|--------------------------------------------------------------------------
	| Authorization - Filters
	|--------------------------------------------------------------------------
	|
	| You may apply different filters to different URI routes to manage
	| role authorization for the whole CMS.
	|
	*/

	'auth_filters' => [
		'x'        => ['admin'],
		'menus'    => ['admin'],
		'pages'    => ['admin'],
		'files'    => ['admin'],
		'users'    => ['admin'],
		'settings' => ['admin'],
	],

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

	'enable_menu_item_auth_filters'         => true,
	'menu_item_auth_filters_developer_only' => true,

	/*
	|--------------------------------------------------------------------------
	| Display Version
	|--------------------------------------------------------------------------
	|
	| Set this to false if you would prefer not to show Fractal's version
	| number in the footer of the supplied layout.
	|
	*/

	'display_version' => true,

];
