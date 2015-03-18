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

use Regulus\ActivityLog\Models\Activity;
use Auth;
use Form;
use Format;
use Site;

class TypesController extends MediaController {

	public function __construct()
	{
		parent::__construct();

		Fractal::setControllerPath($this);

		Site::set('section', 'Media');
		Site::set('subSection', 'Types');
		Site::setTitle(Fractal::transChoice('labels.media_type', 2));

		// set content type and views location
		Fractal::setContentType('media-type');

		Fractal::setViewsLocation('media.types');

		Fractal::addTrailItem(Fractal::transChoice('labels.type', 2), Fractal::getControllerPath());
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
			'label' => Fractal::trans('labels.create_item', ['item' => Fractal::transChoice('labels.media_type')]),
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
		Site::setTitle(Fractal::trans('labels.create_item', ['item' => Fractal::transChoice('labels.media_type')]));

		Type::setDefaultsForNew();
		Form::setErrors();

		Fractal::addButton([
			'label' => Fractal::trans('labels.return_to_items_list', ['items' => Fractal::transChoice('labels.media_type', 2)]),
			'icon'  => 'glyphicon glyphicon-list',
			'uri'   => Fractal::uri('', true),
		]);

		Fractal::addTrailItem(Fractal::trans('labels.create'), Request::url());

		return View::make(Fractal::view('form'));
	}

	public function store()
	{
		Form::setValidationRules(Type::validationRules());

		$messages = [];
		if (Form::validated())
		{
			$messages['success'] = Fractal::trans('messages.success.created', ['item' => Fractal::transChoiceLowerA('labels.media_type')]);

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
			$messages['error'] = Fractal::trans('messages.errors.general');
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
				'error' => Fractal::trans('messages.errors.not_found', ['item' => Fractal::transChoiceLower('labels.media_type')])
			]);

		Site::setTitle($type->name.' ('.Fractal::transChoice('labels.media_type').')');
		Site::setHeading(Fractal::trans('labels.update_item', ['item' => Fractal::transChoice('labels.media_type')]).': <strong>'.Format::entities($type->name).'</strong>');

		Form::setDefaults($type);
		Form::setErrors();

		Fractal::addButton([
			'label' => Fractal::trans('labels.return_to_items_list', ['items' => Fractal::transChoice('labels.media_type', 2)]),
			'icon'  => 'glyphicon glyphicon-list',
			'uri'   => Fractal::uri('', true),
		]);

		Fractal::addTrailItem(Fractal::trans('labels.update'), Request::url());

		return View::make(Fractal::view('form'))
			->with('update', true)
			->with('id', $type->id);
	}

	public function update($slug)
	{
		$type = Type::findBySlug($slug);
		if (empty($type))
			return Redirect::to(Fractal::uri('pages'))->with('messages', [
				'error' => Fractal::trans('messages.errors.not_found', ['item' => Fractal::transChoiceLower('labels.media_type')])
			]);

		$type->setValidationRules();

		$messages = [];
		if (Form::validated())
		{
			$messages['success'] = Fractal::trans('messages.success.updated', ['item' => Fractal::transChoiceLowerA('labels.media_type')]);

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
		$result['message']    = Fractal::trans('messages.success.deleted', ['item' => '<strong>'.$type->name.'</strong>']);

		$type->delete();

		return $result;
	}

}