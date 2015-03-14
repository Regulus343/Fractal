<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Enabled
	|--------------------------------------------------------------------------
	|
	| Whether or not the blogs are enabled.
	|
	*/
	'enabled' => true,

	/*
	|--------------------------------------------------------------------------
	| Subdomain
	|--------------------------------------------------------------------------
	|
	| The subdomain for the blog URLs. The default is "blog", so the blog will
	| be mapped to "http://blog.website.com". Set to false or blank to not use
	| a subdomain.
	|
	*/
	'subdomain' => 'blog',

	/*
	|--------------------------------------------------------------------------
	| Base URI
	|--------------------------------------------------------------------------
	|
	| The URI for the blog. The default is false as the blog is configured by
	| default to use a subdomain instead. If you would like to have a URL like
	| "http://website.com/blog" instead, set this to "blog" and set "subdomain"
	| to false.
	|
	*/
	'base_uri' => false,

	/*
	|--------------------------------------------------------------------------
	| Short Routes
	|--------------------------------------------------------------------------
	|
	| Whether to turn on short routes for article URLs. If short routes is
	| turned on, blog.website.com/example-item will work along with the
	| blog.website.com/item/example-item and blog.website.com/i/example-item
	| routes that are automatically set. The ID routes will work as well, such
	| as blog.website.com/1. The example URLs are assuming default subdomain
	| and base URI settings.
	|
	*/
	'short_routes' => true,

	/*
	|--------------------------------------------------------------------------
	| Blog View Controller
	|--------------------------------------------------------------------------
	|
	| The controller for viewing the blog. If you have your own custom
	| controller, point this setting to it instead of the default.
	|
	*/
	'view_controller' => 'Regulus\Fractal\Controllers\Blogs\ViewController',

	/*
	|--------------------------------------------------------------------------
	| Views Location
	|--------------------------------------------------------------------------
	|
	| The location of your blog views. It is defaulted to "fractal::" to use
	| Fractal's built-in views, but you may point it towards a views directory
	| of your own for full view customization.
	|
	*/
	'views_location' => 'fractal::blogs.view',

	/*
	|--------------------------------------------------------------------------
	| Multiple Blogs
	|--------------------------------------------------------------------------
	|
	| Set this to true to set up your website for multiple blogs. Leave this as
	| false to set it up for a single blog.
	|
	*/
	'multiple' => false,

	/*
	|--------------------------------------------------------------------------
	| Preview Divider
	|--------------------------------------------------------------------------
	|
	| This is the preview divider for dividing an article into content that is
	| visible on the blog's main page and content that is only visible on the
	| article page by clicking the article's title or a "Read More" link.
	|
	*/
	'preview_divider' => '[preview-divider]',

	/*
	|--------------------------------------------------------------------------
	| Use Standard Layout For Article List
	|--------------------------------------------------------------------------
	|
	| If true, the standard single content area layout will be used for an
	| article's display in the article list and the content area will be chosen
	| that has a layout tag of "main" or "primary" if one exists. The alternate
	| layout will still be used for a specific article's page. If this is set
	| to false, the selected layout will be used for the article list as well.
	|
	*/
	'use_standard_layout_for_article_list' => true,

	/*
	|--------------------------------------------------------------------------
	| Placeholder Thumbnail Image for Blog Articles
	|--------------------------------------------------------------------------
	|
	| The path and filename of the placeholder thumbnail image. If no extension
	| is given, PNG will be assumed. You may use a composer package path by
	| preceding the file path with, for example, "regulus/fractal::".
	|
	*/
	'placeholder_thumbnail_image' => 'regulus/fractal::article-placeholder',

];
