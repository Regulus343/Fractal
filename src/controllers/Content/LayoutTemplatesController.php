<?php namespace Regulus\Fractal\Controllers\Content;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

use Fractal;

use Regulus\Fractal\Models\Content\LayoutTemplate;

use Regulus\ActivityLog\Activity;
use \Auth;
use \Form;
use \Format;
use \Site;

use Regulus\Fractal\Controllers\BaseController;

class LayoutTemplatesController extends BaseController {

	public function __construct()
	{
		parent::__construct();

		Fractal::setControllerPath($this);

		Site::set('section', 'Content');
		Site::set('subSection', 'Layout Templates');
		Site::set('title', Fractal::lang('labels.layoutTemplates'));

		//set content type and views location
		Fractal::setContentType('layout-template');

		Fractal::setViewsLocation('content.layout_templates');

		Fractal::addTrailItem(Fractal::lang('labels.layoutTemplates'), Fractal::getControllerPath());
	}

	public function index()
	{
		$data       = Fractal::setupPagination();
		$categories = LayoutTemplate::getSearchResults($data);

		Fractal::setContentForPagination($categories);

		$data     = Fractal::setPaginationMessage(true);
		$messages = Fractal::getPaginationMessageArray();

		if (!count($categories))
			$categories = LayoutTemplate::orderBy($data['sortField'], $data['sortOrder'])->paginate($data['itemsPerPage']);

		Fractal::addButton([
			'label' => Fractal::lang('labels.createLayoutTemplate'),
			'icon'  => 'glyphicon glyphicon-th',
			'uri'   => Fractal::uri('create', true),
		]);

		return View::make(Fractal::view('list'))
			->with('content', $categories)
			->with('messages', $messages);
	}

	public function search()
	{
		$data       = Fractal::setupPagination();
		$categories = LayoutTemplate::getSearchResults($data);

		Fractal::setContentForPagination($categories);

		$data = Fractal::setPaginationMessage();

		if (!count($categories))
			$data['content'] = LayoutTemplate::orderBy($data['sortField'], $data['sortOrder'])->paginate($data['itemsPerPage']);

		$data['result']['pages']     = Fractal::getLastPage();
		$data['result']['tableBody'] = Fractal::createTable($data['content'], true);

		return $data['result'];
	}

	public function create()
	{
		Site::set('title', Fractal::lang('labels.createLayoutTemplate'));

		LayoutTemplate::setDefaultsForNew();
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
		Form::setValidationRules(LayoutTemplate::validationRules());

		$messages = [];
		if (Form::validated()) {
			$messages['success'] = Fractal::lang('messages.successCreated', ['item' => Fractal::langLowerA('labels.category')]);

			$input = Input::all();
			$input['user_id'] = Auth::user()->id;

			$layoutTemplate = LayoutTemplate::createNew($input);

			Activity::log([
				'contentId'   => $layoutTemplate->id,
				'contentType' => 'LayoutTemplate',
				'action'      => 'Create',
				'description' => 'Created a Layout Template',
				'details'     => 'Name: '.$layoutTemplate->name,
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

	public function edit($id)
	{
		$layoutTemplate = LayoutTemplate::where('id', $id)->where('static', false)->first();
		if (empty($layoutTemplate))
			return Redirect::to(Fractal::uri('pages'))->with('messages', [
				'error' => Fractal::lang('messages.errorNotFound', ['item' => Fractal::langLower('labels.category')])
			]);

		Site::set('title', $layoutTemplate->name.' ('.Fractal::lang('labels.category').')');
		Site::set('titleHeading', Fractal::lang('labels.updateLayoutTemplate').': <strong>'.Format::entities($layoutTemplate->name).'</strong>');

		Form::setDefaults($layoutTemplate);
		Form::setErrors();

		Fractal::addButton([
			'label' => Fractal::lang('labels.returnToCategoriesList'),
			'icon'  => 'glyphicon glyphicon-list',
			'uri'   => Fractal::uri('', true),
		]);

		Fractal::addTrailItem(Fractal::lang('labels.update'), Request::url());

		return View::make(Fractal::view('form'))
			->with('update', true)
			->with('id', $layoutTemplate->id);
	}

	public function update($id)
	{
		$layoutTemplate = LayoutTemplate::where('id', $id)->where('static', false)->first();
		if (empty($layoutTemplate))
			return Redirect::to(Fractal::uri('pages'))->with('messages', [
				'error' => Fractal::lang('messages.errorNotFound', ['item' => Fractal::langLower('labels.category')])
			]);

		$layoutTemplate->setValidationRules();

		$messages = [];
		if (Form::validated()) {
			$messages['success'] = Fractal::lang('messages.successUpdated', ['item' => Fractal::langLowerA('labels.category')]);

			$layoutTemplate->saveData();

			Activity::log([
				'contentId'   => $layoutTemplate->id,
				'contentType' => 'LayoutTemplate',
				'action'      => 'Update',
				'description' => 'Updated a Layout Template',
				'details'     => 'Name: '.$layoutTemplate->name,
				'updated'     => true,
			]);

			return Redirect::to(Fractal::uri('', true))
				->with('messages', $messages);
		} else {
			$messages['error'] = Fractal::lang('messages.errorGeneral');

			return Redirect::to(Fractal::uri($id.'/edit', true))
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

		$layoutTemplate = LayoutTemplate::find($id);
		if (empty($layoutTemplate))
			return $result;

		if ($layoutTemplate->pages()->count())
			return $result;

		if ($layoutTemplate->articles()->count())
			return $result;

		Activity::log([
			'contentId'   => $layoutTemplate->id,
			'contentType' => 'LayoutTemplate',
			'action'      => 'Delete',
			'description' => 'Deleted a Layout Template',
			'details'     => 'Name: '.$layoutTemplate->name,
		]);

		$result['resultType'] = "Success";
		$result['message']    = Fractal::lang('messages.successDeleted', ['item' => '<strong>'.$layoutTemplate->name.'</strong>']);

		$layoutTemplate->delete();

		return $result;
	}

}