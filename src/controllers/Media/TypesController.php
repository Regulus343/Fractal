<?php namespace Regulus\Fractal\Controllers\Media;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

use Fractal;

use Regulus\Fractal\Models\Media\Type;
use Regulus\Fractal\Models\Content\FileType;

use Regulus\ActivityLog\Activity;
use \Auth;
use \Form;
use \Format;
use \Site;

use Regulus\Fractal\Controllers\BaseController;

class TypesController extends BaseController {

	public function __construct()
	{
		parent::__construct();

		Fractal::setControllerPath($this);

		Site::set('section', 'Media');
		Site::set('subSection', 'Types');
		Site::set('title', Fractal::lang('labels.mediaTypes'));

		//set content type and views location
		Fractal::setContentType('media-type');

		Fractal::setViewsLocation('media.types');

		Fractal::addTrailItem(Fractal::lang('labels.mediaTypes'), Fractal::getControllerPath());
	}

	public function index()
	{
		$data       = Fractal::setupPagination();
		$categories = Type::getSearchResults($data);

		Fractal::setContentForPagination($categories);

		$data     = Fractal::setPaginationMessage(true);
		$messages = Fractal::getPaginationMessageArray();

		if (!count($categories))
			$categories = Type::orderBy($data['sortField'], $data['sortOrder'])->paginate($data['itemsPerPage']);

		Fractal::addButton([
			'label' => Fractal::lang('labels.createType'),
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
		$categories = Type::getSearchResults($data);

		Fractal::setContentForPagination($categories);

		$data = Fractal::setPaginationMessage();

		if (!count($categories))
			$data['content'] = Type::orderBy($data['sortField'], $data['sortOrder'])->paginate($data['itemsPerPage']);

		$data['result']['pages']     = Fractal::getLastPage();
		$data['result']['tableBody'] = Fractal::createTable($data['content'], true);

		return $data['result'];
	}

	public function create()
	{
		Site::set('title', Fractal::lang('labels.createType'));

		Type::setDefaultsForNew();
		Form::setErrors();

		Fractal::addButton([
			'label' => Fractal::lang('labels.returnToCategoriesList'),
			'icon'  => 'glyphicon glyphicon-list',
			'uri'   => Fractal::uri('', true),
		]);

		Fractal::addTrailItem(Fractal::lang('labels.create'), Request::url());

		return View::make(Fractal::view('form'));
	}

	public function store()
	{
		Form::setValidationRules(Type::validationRules());

		$messages = [];
		if (Form::validated()) {
			$messages['success'] = Fractal::lang('messages.successCreated', ['item' => Fractal::langLowerA('labels.category')]);

			$input = Input::all();
			$input['user_id'] = Auth::user()->id;

			$type = Type::createNew($input);

			Activity::log([
				'contentId'   => $type->id,
				'contentType' => 'Type',
				'action'      => 'Create',
				'description' => 'Created a Media Type',
				'details'     => 'Name: '.$type->name,
			]);

			return Redirect::to(Fractal::uri('', true))
				->with('messages', $messages);
		} else {
			$messages['error'] = Fractal::lang('messages.errorGeneral');
		}

		return Redirect::to(Fractal::uri('create', true))
			->with('messages', $messages)
			->with('errors', Form::getErrors())
			->withInput();
	}

	public function edit($slug)
	{
		$type = Type::findBySlug($slug);
		if (empty($type))
			return Redirect::to(Fractal::uri('pages'))->with('messages', [
				'error' => Fractal::lang('messages.errorNotFound', ['item' => Fractal::langLower('labels.category')])
			]);

		Site::set('title', $type->name.' ('.Fractal::lang('labels.category').')');
		Site::set('titleHeading', Fractal::lang('labels.updateType').': <strong>'.Format::entities($type->name).'</strong>');

		Form::setDefaults($type);
		Form::setErrors();

		Fractal::addButton([
			'label' => Fractal::lang('labels.returnToCategoriesList'),
			'icon'  => 'glyphicon glyphicon-list',
			'uri'   => Fractal::uri('', true),
		]);

		Fractal::addTrailItem(Fractal::lang('labels.update'), Request::url());

		return View::make(Fractal::view('form'))
			->with('update', true)
			->with('id', $type->id);
	}

	public function update($slug)
	{
		$type = Type::findBySlug($slug);
		if (empty($type))
			return Redirect::to(Fractal::uri('pages'))->with('messages', [
				'error' => Fractal::lang('messages.errorNotFound', ['item' => Fractal::langLower('labels.category')])
			]);

		$type->setValidationRules();

		$messages = [];
		if (Form::validated()) {
			$messages['success'] = Fractal::lang('messages.successUpdated', ['item' => Fractal::langLowerA('labels.category')]);

			$type->saveData();

			Activity::log([
				'contentId'   => $type->id,
				'contentType' => 'Type',
				'action'      => 'Update',
				'description' => 'Updated a Media Type',
				'details'     => 'Name: '.$type->name,
				'updated'     => true,
			]);

			return Redirect::to(Fractal::uri('', true))
				->with('messages', $messages);
		} else {
			$messages['error'] = Fractal::lang('messages.errorGeneral');

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
			'message'    => Fractal::lang('messages.errorGeneral'),
		];

		$type = Type::find($id);
		if (empty($type))
			return $result;

		if ($type->items()->count())
			return $result;

		Activity::log([
			'contentId'   => $type->id,
			'contentType' => 'Type',
			'action'      => 'Delete',
			'description' => 'Deleted a Media Type',
			'details'     => 'Name: '.$type->name,
		]);

		$result['resultType'] = "Success";
		$result['message']    = Fractal::lang('messages.successDeleted', ['item' => '<strong>'.$type->name.'</strong>']);

		$type->delete();

		return $result;
	}

}