<?php namespace Regulus\Fractal\Controllers\Media;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

use Fractal;

use Regulus\Fractal\Models\Media\Set;
use Regulus\Fractal\Models\Media\Item;

use Regulus\ActivityLog\Activity;
use \Auth;
use \Form;
use \Format;
use \Site;

use Regulus\Fractal\Controllers\BaseController;

class SetsController extends BaseController {

	public function __construct()
	{
		parent::__construct();

		Fractal::setControllerPath($this);

		Site::set('section', 'Media');
		Site::set('subSection', 'Sets');
		Site::set('title', Fractal::lang('labels.mediaSets'));

		//set content type and views location
		Fractal::setContentType('media-set');

		Fractal::setViewsLocation('media.sets');

		Fractal::addTrailItem(Fractal::lang('labels.mediaSets'), Fractal::getControllerPath());
	}

	public function index()
	{
		$data       = Fractal::setupPagination();
		$categories = Set::getSearchResults($data);

		Fractal::setContentForPagination($categories);

		$data     = Fractal::setPaginationMessage(true);
		$messages = Fractal::getPaginationMessageArray();

		if (!count($categories))
			$categories = Set::orderBy($data['sortField'], $data['sortOrder'])->paginate($data['itemsPerPage']);

		Fractal::addButton([
			'label' => Fractal::lang('labels.createSet'),
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
		$categories = Set::getSearchResults($data);

		Fractal::setContentForPagination($categories);

		$data = Fractal::setPaginationMessage();

		if (!count($categories))
			$data['content'] = Set::orderBy($data['sortField'], $data['sortOrder'])->paginate($data['itemsPerPage']);

		$data['result']['pages']     = Fractal::getLastPage();
		$data['result']['tableBody'] = Fractal::createTable($data['content'], true);

		return $data['result'];
	}

	public function create()
	{
		Site::set('title', Fractal::lang('labels.createSet'));
		Site::set('wysiwyg', true);

		Set::setDefaultsForNew();
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
		Form::setValidationRules(Set::validationRules());

echo '<pre>'; var_dump(Input::all()); echo '</pre>'; exit;

		$messages = [];
		if (Form::validated()) {
			$messages['success'] = Fractal::lang('messages.successCreated', ['item' => Fractal::langLowerA('labels.category')]);

			$input = Input::all();
			$input['user_id'] = Auth::user()->id;

			$set = Set::createNew($input);

			Activity::log([
				'contentId'   => $set->id,
				'contentType' => 'Set',
				'action'      => 'Create',
				'description' => 'Created a Media Set',
				'details'     => 'Name: '.$set->name,
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
		$set = Set::findBySlug($slug);
		if (empty($set))
			return Redirect::to(Fractal::uri('pages'))->with('messages', [
				'error' => Fractal::lang('messages.errorNotFound', ['item' => Fractal::langLower('labels.category')])
			]);

		Site::set('title', $set->name.' ('.Fractal::lang('labels.category').')');
		Site::set('titleHeading', Fractal::lang('labels.updateSet').': <strong>'.Format::entities($set->name).'</strong>');
		Site::set('wysiwyg', true);

		Form::setDefaults($set);
		Form::setErrors();

		Fractal::addButton([
			'label' => Fractal::lang('labels.returnToCategoriesList'),
			'icon'  => 'glyphicon glyphicon-list',
			'uri'   => Fractal::uri('', true),
		]);

		Fractal::addTrailItem(Fractal::lang('labels.update'), Request::url());

		return View::make(Fractal::view('form'))
			->with('update', true)
			->with('id', $set->id);
	}

	public function update($id)
	{
		$set = Set::findBySlug($slug);
		if (empty($set))
			return Redirect::to(Fractal::uri('pages'))->with('messages', [
				'error' => Fractal::lang('messages.errorNotFound', ['item' => Fractal::langLower('labels.category')])
			]);

		$set->setValidationRules();

		$messages = [];
		if (Form::validated()) {
			$messages['success'] = Fractal::lang('messages.successUpdated', ['item' => Fractal::langLowerA('labels.category')]);

			$set->saveData();

			Activity::log([
				'contentId'   => $set->id,
				'contentType' => 'Set',
				'action'      => 'Update',
				'description' => 'Updated a Media Set',
				'details'     => 'Name: '.$set->name,
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

		$set = Set::find($id);
		if (empty($set))
			return $result;

		if ($set->articles()->count())
			return $result;

		Activity::log([
			'contentId'   => $set->id,
			'contentType' => 'Set',
			'action'      => 'Delete',
			'description' => 'Deleted a Media Set',
			'details'     => 'Name: '.$set->name,
		]);

		$result['resultType'] = "Success";
		$result['message']    = Fractal::lang('messages.successDeleted', ['item' => '<strong>'.$set->name.'</strong>']);

		$set->delete();

		return $result;
	}

	public function addItem($id = null)
	{
		$data = [
			'title'      => Fractal::lang('labels.addMediaItem'),
			'setId'      => $id,
			'mediaItems' => Item::orderBy('title')->get(),
		];

		return Fractal::modalView('add_item', $data);
	}

}