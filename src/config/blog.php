<?php

return array(

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
	'baseUri' => false,

	/*
	|--------------------------------------------------------------------------
	| Blog View Controller
	|--------------------------------------------------------------------------
	|
	| The URI for content pages on the website. By default, it is left blank to
	| define the page URI at the root of the website. For example "" would give
	| you a URL like "http://localhost/home" and "page" would give you
	| "http://localhost/page/home".
	|
	*/
	'viewController' => 'Regulus\Fractal\Controllers\Blogs\ViewController',

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
	'previewDivider' => '[preview-divider]',

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
	'useStandardLayoutForArticleList' => true,

);