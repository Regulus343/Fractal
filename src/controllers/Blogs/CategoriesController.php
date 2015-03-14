<?php namespace Regulus\Fractal\Controllers\Blogs;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

use Fractal;

use Regulus\Fractal\Models\Blogs\Category;

use Regulus\ActivityLog\Activity;
use \Auth;
use \Form;
use \Format;
use \Site;

class CategoriesController extends BlogsController {

	public function __construct()
	{
		parent::__construct();

		Fractal::setControllerPath($this);

		Site::set('section', 'Blogs');
		Site::set('subSection', 'Categories');
		Site::setTitle(Fractal::transChoice('labels.blog_categories', 2));

		// set content type and views location
		Fractal::setContentType('blog-category');

		Fractal::setViewsLocation('blogs.categories');

		Fractal::addTrailItem(Fractal::trans('labels.categories'), Fractal::getControllerPath());
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
			'label' => Fractal::trans('labels.create_item', ['item' => Fractal::trans('labels.blog_category')]),
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
		Site::setTitle(Fractal::trans('labels.createCategory'));

		Category::setDefaultsForNew();
		Form::setErrors();

		Fractal::addButton([
			'label' => Fractal::trans('labels.returnToCategoriesList'),
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
		if (Form::validated()) {
			$messages['success'] = Fractal::trans('messages.successCreated', ['item' => Fractal::transLowerA('labels.category')]);

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
			$messages['error'] = Fractal::trans('messages.errorGeneral');
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
				'error' => Fractal::trans('messages.errorNotFound', ['item' => Fractal::transLower('labels.category')])
			]);

		Site::setTitle($category->name.' ('.Fractal::trans('labels.category').')');
		Site::setHeading(Fractal::trans('labels.updateCategory').': <strong>'.Format::entities($category->name).'</strong>');

		Form::setDefaults($category);
		Form::setErrors();

		Fractal::addButton([
			'label' => Fractal::trans('labels.returnToCategoriesList'),
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
				'error' => Fractal::trans('messages.errorNotFound', ['item' => Fractal::transLower('labels.category')])
			]);

		$category->setValidationRules();

		$messages = [];
		if (Form::validated()) {
			$messages['success'] = Fractal::trans('messages.successUpdated', ['item' => Fractal::transLowerA('labels.category')]);

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
			$messages['error'] = Fractal::trans('messages.errorGeneral');

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
			'message'    => Fractal::trans('messages.errorGeneral'),
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
		$result['message']    = Fractal::trans('messages.successDeleted', ['item' => '<strong>'.$category->name.'</strong>']);

		$category->articles()->sync([]);
		$category->delete();

		return $result;
	}

}