<?php namespace Regulus\Fractal\Controllers\Blogs;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

use Fractal;

use Regulus\Fractal\Models\Blog\Category;

use Regulus\ActivityLog\Models\Activity;
use Auth;
use Form;
use Format;
use Site;

class CategoriesController extends BlogsController {

	public function __construct()
	{
		parent::__construct();

		Fractal::setControllerPath($this);

		Site::set('section', 'Blogs');
		Site::set('subSection', 'Categories');
		Site::setTitle(Fractal::transChoice('labels.blog_category', 2));

		// set content type and views location
		Fractal::setContentType('blog-category');

		Fractal::setViewsLocation('blogs.categories');

		Fractal::addTrailItem(Fractal::transChoice('labels.category', 2), Fractal::getControllerPath());
	}

	public function index()
	{
		$data       = Fractal::setupPagination();
		$categories = Category::getSearchResults($data);

		Fractal::setContentForPagination($categories);

		$data     = Fractal::setPaginationMessage(true);
		$messages = Fractal::getPaginationMessageArray();

		if (!count($categories))
			$categories = Category::orderBy($data['sortField'], $data['sortOrder'])->paginate($data['itemsPerPage']);

		Fractal::addButton([
			'label' => Fractal::trans('labels.create_item', ['item' => Fractal::transChoice('labels.category')]),
			'icon'  => 'glyphicon glyphicon-file',
			'uri'   => Fractal::uri('create', true),
		]);

		return View::make(Fractal::view('list'))
			->with('content', $categories)
			->with('messages', $messages);
	}

	public function search()
	{
		$data       = Fractal::setupPagination();
		$categories = Category::getSearchResults($data);

		Fractal::setContentForPagination($categories);

		$data = Fractal::setPaginationMessage();

		if (!count($categories))
			$data['content'] = Category::orderBy($data['sortField'], $data['sortOrder'])->paginate($data['itemsPerPage']);

		$data['result']['pages']     = Fractal::getLastPage();
		$data['result']['tableBody'] = Fractal::createTable($data['content'], true);

		return $data['result'];
	}

	public function create()
	{
		Site::setTitle(Fractal::trans('labels.create_item', ['item' => Fractal::transChoice('labels.category')]));

		Category::setDefaultsForNew();
		Form::setErrors();

		Fractal::addButton([
			'label' => Fractal::trans('labels.return_to_items_list', ['item' => Fractal::transChoice('labels.category', 2)]),
			'icon'  => 'glyphicon glyphicon-list',
			'uri'   => Fractal::uri('', true),
		]);

		Fractal::addTrailItem(Fractal::trans('labels.create'), Request::url());

		return View::make(Fractal::view('form'));
	}

	public function store()
	{
		Form::setValidationRules(Category::validationRules());

		$messages = [];
		if (Form::validated())
		{
			$messages['success'] = Fractal::trans('messages.success.created', ['item' => Fractal::transChoiceLowerA('labels.category')]);

			$input = Input::all();
			$input['user_id'] = Auth::user()->id;

			$category = Category::createNew($input);

			Activity::log([
				'contentId'   => $category->id,
				'contentType' => 'Category',
				'action'      => 'Create',
				'description' => 'Created a Category',
				'details'     => 'Name: '.$category->name,
			]);

			return Redirect::to(Fractal::uri('', true))
				->with('messages', $messages);
		} else {
			$messages['error'] = Fractal::trans('messages.errors.general');
		}

		return Redirect::to(Fractal::uri('create', true))
			->with('messages', $messages)
			->with('errors', Form::getErrors())
			->withInput();
	}

	public function edit($slug)
	{
		$category = Category::findBySlug($slug);
		if (empty($category))
			return Redirect::to(Fractal::uri('pages'))->with('messages', [
				'error' => Fractal::trans('messages.errors.not_found', ['item' => Fractal::transChoiceLower('labels.category')])
			]);

		Site::setTitle($category->name.' ('.Fractal::transChoice('labels.category').')');
		Site::setHeading(Fractal::trans('labels.update_item', ['item' => Fractal::transChoice('labels.category')]).': <strong>'.Format::entities($category->name).'</strong>');

		Form::setDefaults($category);
		Form::setErrors();

		Fractal::addButton([
			'label' => Fractal::trans('labels.return_to_items_list', ['item' => Fractal::transChoice('labels.category', 2)]),
			'icon'  => 'glyphicon glyphicon-list',
			'uri'   => Fractal::uri('', true),
		]);

		Fractal::addTrailItem(Fractal::trans('labels.update'), Request::url());

		return View::make(Fractal::view('form'))
			->with('update', true)
			->with('id', $category->id);
	}

	public function update($slug)
	{
		$category = Category::findBySlug($slug);
		if (empty($category))
			return Redirect::to(Fractal::uri('pages'))->with('messages', [
				'error' => Fractal::trans('messages.errors.not_found', ['item' => Fractal::transChoiceLower('labels.category')])
			]);

		$category->setValidationRules();

		$messages = [];
		if (Form::validated())
		{
			$messages['success'] = Fractal::trans('messages.success.updated', ['item' => Fractal::transChoiceLowerA('labels.category')]);

			$category->saveData();

			Activity::log([
				'contentId'   => $category->id,
				'contentType' => 'Category',
				'action'      => 'Update',
				'description' => 'Updated a Category',
				'details'     => 'Name: '.$category->name,
				'updated'     => true,
			]);

			return Redirect::to(Fractal::uri('', true))
				->with('messages', $messages);
		} else {
			$messages['error'] = Fractal::trans('messages.errors.general');

			return Redirect::to(Fractal::uri($slug.'/edit', true))
				->with('messages', $messages)
				->with('errors', Form::getErrors())
				->withInput();
		}
	}

	public function destroy($id)
	{
		$result = [
			'resultType' => 'Error',
			'message'    => Fractal::trans('messages.errors.general'),
		];

		$category = Category::find($id);
		if (empty($category))
			return $result;

		Activity::log([
			'contentId'   => $category->id,
			'contentType' => 'Category',
			'action'      => 'Delete',
			'description' => 'Deleted a Category',
			'details'     => 'Name: '.$category->name,
		]);

		$result['resultType'] = "Success";
		$result['message']    = Fractal::trans('messages.success.deleted', ['item' => '<strong>'.$category->name.'</strong>']);

		$category->articles()->sync([]);
		$category->delete();

		return $result;
	}

}