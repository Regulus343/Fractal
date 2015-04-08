<?php namespace Regulus\Fractal;

/*
|--------------------------------------------------------------------------
| View Composers
|--------------------------------------------------------------------------
|
| View composers for the CMS.
|
*/

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;

use Auth;
use Form;
use Site;

use Regulus\Fractal\Models\Content\Page;
use Regulus\Fractal\Models\Content\FileType;
use Regulus\Fractal\Models\Media\Type as MediaType;
use Regulus\Fractal\Models\Media\Set as MediaSet;
use Regulus\Fractal\Models\Blog\Category as BlogCategory;

use Regulus\Identify\Models\User;
use Regulus\Identify\Models\Role;

$viewsLocation = config('cms.views_location');

View::composer($viewsLocation.'partials.messages', function($view)
{
	$sessionMessages = Session::get('messages');
	$view->with('sessionMessages', $sessionMessages);
});

View::composer($viewsLocation.'content.menus.form', function($view)
{
	$typeOptions = Form::simpleOptions(['URI', 'Content Page']);
	$pageOptions = Form::prepOptions(Page::select('id', 'title')->orderBy('title')->get(), array('id', 'title'));

	$view
		->with('typeOptions', $typeOptions)
		->with('pageOptions', $pageOptions);
});

View::composer([$viewsLocation.'content.files.form', $viewsLocation.'media.items.form'], function($view)
{
	$view->with('fileTypeExtensions', FileType::getExtensionsForIds());
});

View::composer($viewsLocation.'media.items.form', function($view)
{
	$mediaTypes = MediaType::orderBy('name');

	if (Form::value('file_type_id') != "")
		$mediaTypes
			->where('file_type_id', Form::value('file_type_id'))
			->orWhereNull('file_type_id');

	$mediaTypeOptions = Form::prepOptions($mediaTypes->get(), ['id', 'name']);

	$view->with('mediaTypeOptions', $mediaTypeOptions);
});

View::composer($viewsLocation.'media.view.partials.nav.types', function($view)
{
	$mediaTypes = MediaType::orderBy('name')->get();

	$view->with('mediaTypes', $mediaTypes);
});

View::composer($viewsLocation.'media.view.partials.nav.sets', function($view)
{
	$mediaSets = MediaSet::orderBy('title');

	if (Auth::isNot('admin'))
		$mediaSets->onlyPublished();

	$mediaSets = $mediaSets->get();

	$view->with('mediaSets', $mediaSets);
});

View::composer($viewsLocation.'blogs.view.partials.nav.categories', function($view)
{
	$categories = BlogCategory::orderBy('name')->get();

	$view->with('categories', $categories);
});

View::composer($viewsLocation.'public.partials.latest_content', function($view)
{
	$latestContent = Facade::getLatestContent();

	$view->with('latestContent', $latestContent);
});