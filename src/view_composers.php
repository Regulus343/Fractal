<?php namespace Regulus\Fractal;

/*
|--------------------------------------------------------------------------
| View Composers
|--------------------------------------------------------------------------
|
| The view composers for the CMS.
|
*/

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;

use \Site as Site;
use \Form as Form;

use Regulus\Fractal\Models\Content\Page;
use Regulus\Fractal\Models\Content\FileType;
use Regulus\Fractal\Models\Media\Type as MediaType;
use Regulus\Fractal\Models\Blogs\Category as BlogCategory;

use Regulus\Identify\User;
use Regulus\Identify\Role;

$viewsLocation = Config::get('fractal::viewsLocation');

View::composer($viewsLocation.'partials.messages', function($view)
{
	$sessionMessages = Session::get('messages');
	$view->with('sessionMessages', $sessionMessages);
});

View::composer($viewsLocation.'content.menus.form', function($view)
{
	$typeOptions = Form::simpleOptions(array('URI', 'Content Page'));
	$pageOptions = Form::prepOptions(Page::select('id', 'title')->orderBy('title')->get(), array('id', 'title'));

	$view
		->with('typeOptions', $typeOptions)
		->with('pageOptions', $pageOptions);
});

View::composer(array($viewsLocation.'content.files.form', $viewsLocation.'media.items.form'), function($view)
{
	$view->with('fileTypeExtensions', FileType::getExtensionsForIds());
});

View::composer($viewsLocation.'media.items.form', function($view)
{
	$mediaTypes = MediaType::orderBy('name');

	if (Form::value('file_type_id') != "")
		$mediaTypes->where('file_type_id', Form::value('file_type_id'));

	$mediaTypeOptions = Form::prepOptions($mediaTypes->get(), array('id', 'name'));

	$view->with('mediaTypeOptions', $mediaTypeOptions);
});

View::composer($viewsLocation.'blogs.view.partials.nav.categories', function($view)
{
	$categories = BlogCategory::orderBy('name')->get();

	$view->with('categories', $categories);
});