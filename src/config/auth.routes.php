<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Route Permissions
	|--------------------------------------------------------------------------
	|
	| The route name in the index corresponds to the "as" name of the route
	| action. If you don't know what this is for a specific route, you may
	| refer to Request::route()->getAction().
	|
	| The values correspond to a permission (a string) or a set of permissions
	| (an array). As long as the user has one of the required permissions, they
	| will be considered authorized. If you would like the user to require all
	| permissions, simply add one more item to the the array: "[ALL]".
	|
	| You may also use partial routes using an asterisk (*) such as "admin.*".
	|
	*/

	'settings.*'              => ['manage-settings', 'demo'],

	'menus.*'                 => ['manage-menus', 'view-menus', 'demo'],

	'pages.*'                 => ['manage-pages', 'demo'],
	'pages.index'             => ['manage-pages', 'view-pages', 'demo'],
	'pages.search'            => ['manage-pages', 'view-pages', 'demo'],
	'pages.view'              => [],

	'files.*'                 => ['manage-files', 'demo'],
	'files.index'             => ['manage-files', 'view-files', 'demo'],
	'files.search'            => ['manage-files', 'view-files', 'demo'],

	'layout-templates.*'      => ['manage-layout-templates', 'demo'],
	'layout-templates.index'  => ['manage-layout-templates', 'view-layout-templates', 'demo'],
	'layout-templates.search' => ['manage-layout-templates', 'view-layout-templates', 'demo'],

	'users.*'                 => ['manage-users', 'demo'],
	'users.activity.*'        => [],

	'media.items.*'           => ['manage-media-items', 'demo'],
	'media.items.index'       => ['manage-media-items', 'view-media-items', 'demo'],
	'media.items.search'      => ['manage-media-items', 'view-media-items', 'demo'],
	'media.items.public.*'    => [],

	'media.types.*'           => ['manage-media-types', 'demo'],
	'media.types.index'       => ['manage-media-types', 'view-media-types', 'demo'],
	'media.types.search'      => ['manage-media-types', 'view-media-types', 'demo'],

	'media.sets.*'            => ['manage-media-sets', 'demo'],
	'media.sets.index'        => ['manage-media-sets', 'view-media-sets', 'demo'],
	'media.sets.search'       => ['manage-media-sets', 'view-media-sets', 'demo'],

	'blogs.articles.*'        => ['manage-blog-articles', 'demo'],
	'blogs.articles.index'    => ['manage-blog-articles', 'view-blog-articles', 'demo'],
	'blogs.articles.search'   => ['manage-blog-articles', 'view-blog-articles', 'demo'],
	'blogs.articles.public.*' => [],

	'blogs.categories.*'      => ['manage-blog-categories', 'demo'],
	'blogs.categories.index'  => ['manage-blog-categories', 'view-blog-categories', 'demo'],
	'blogs.categories.search' => ['manage-blog-categories', 'view-blog-categories', 'demo'],

];
