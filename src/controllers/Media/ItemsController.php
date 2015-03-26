<?php namespace Regulus\Fractal\Controllers\Media;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

use Fractal;

use Regulus\Fractal\Models\Media\Item;
use Regulus\Fractal\Models\Media\Type;
use Regulus\Fractal\Models\Content\FileType;

use Regulus\ActivityLog\Models\Activity;
use Auth;
use Form;
use Format;
use Site;

class ItemsController extends MediaController {

	public function __construct()
	{
		parent::__construct();

		Fractal::setControllerPath($this);

		Site::set('section', 'Media');
		Site::set('subSection', 'Items');
		Site::setTitle(Fractal::transChoice('labels.media_item', 2));

		// set content type and views location
		Fractal::setContentType('media-item');

		Fractal::setViewsLocation('media.items');

		Fractal::addTrailItem(Fractal::transChoice('labels.item'), Fractal::getControllerPath());

		Site::set('defaultSorting', ['order' => 'desc']);
	}

	public function index()
	{
		$data  = Fractal::setupPagination();
		$media = Item::getSearchResults($data);

		Fractal::setContentForPagination($media);

		$data     = Fractal::setPaginationMessage(true);
		$messages = Fractal::getPaginationMessageArray();

		if (!count($media))
			$media = Item::orderBy($data['sortField'], $data['sortOrder'])->paginate($data['itemsPerPage']);

		Fractal::addButton([
			'label' => Fractal::trans('labels.create_item', ['item' => Fractal::transChoice('labels.media_item')]),
			'icon'  => 'file-image-o',
			'uri'   => Fractal::uri('create', true),
		]);

		return View::make(Fractal::view('list'))
			->with('content', $media)
			->with('messages', $messages);
	}

	public function search()
	{
		$data  = Fractal::setupPagination();
		$media = Item::getSearchResults($data);

		Fractal::setContentForPagination($media);

		$data = Fractal::setPaginationMessage();

		if (!count($media))
			$data['content'] = Item::orderBy($data['sortField'], $data['sortOrder'])->paginate($data['itemsPerPage']);

		$data['result']['pages']     = Fractal::getLastPage();
		$data['result']['tableBody'] = Fractal::createTable($data['content'], true);

		return $data['result'];
	}

	public function create()
	{
		Site::setTitle(Fractal::trans('labels.create_item', ['item' => Fractal::transChoice('labels.media_item')]));
		Site::set('wysiwyg', true);

		Item::setDefaultsForNew();

		Fractal::restoreSavedContent();

		$this->setDefaultImageSizes();

		Form::setErrors();

		Fractal::addButton([
			'label' => Fractal::trans('labels.return_to_items_list', ['items' => Fractal::transChoice('labels.media_item', 2)]),
			'icon'  => 'list',
			'uri'   => Fractal::uri('', true),
		]);

		Fractal::addTrailItem(Fractal::trans('labels.create'), Request::url());

		return View::make(Fractal::view('form'));
	}

	public function store()
	{
		$result = Item::validateAndSave();

		if (!$result['error'])
			Fractal::clearSavedContent();

		return Redirect::to(Fractal::uri('create', true))
			->with('messages', $result['messages'])
			->with('errors', Form::getErrors())
			->withInput();
	}

	public function edit($slug)
	{
		$item = Item::findBySlug($slug);
		if (empty($item))
		{
			// attempt to find by ID and redirect to slugged URL if found
			$item = Item::find($slug);

			if (!empty($item))
				return Redirect::to(Fractal::uri($item->slug.'/edit', true));
			else
				return Redirect::to(Fractal::uri('', true))->with('messages', [
					'error' => Fractal::trans('messages.errors.not_found', ['item' => Fractal::transChoiceLower('labels.media_item')])
				]);
		}

		Site::setTitle($item->getTitle().' ('.Fractal::transChoice('labels.media_item').')');
		Site::setHeading(Fractal::trans('labels.update_item', ['item' => Fractal::transChoice('labels.media_item')]).': <strong>'.$item->getTitle().'</strong>');
		Site::set('wysiwyg', true);

		$item->setDefaults();

		$this->setDefaultImageSizes();

		Form::setErrors();

		Fractal::addButtons([
			[
				'label' => Fractal::trans('labels.return_to_items_list', ['item' => Fractal::transChoice('labels.media_item', 2)]),
				'icon'  => 'list',
				'uri'   => Fractal::uri('', true),
			],[
				'label' => Fractal::trans('labels.view_item', ['item' => Fractal::transChoice('labels.media_item')]),
				'icon'  => 'file-image-o',
				'url'   => $item->getUrl(),
			],[
				'label' => Fractal::trans('labels.save'),
				'icon'  => 'save',
				'url'   => '',
				'class' => 'btn btn-green btn-save-content',
			],
		]);

		Fractal::addTrailItem(Fractal::trans('labels.update'), Request::url());

		return View::make(Fractal::view('form'))
			->with('update', true)
			->with('itemUrl', $item->getUrl())
			->with('item', $item);
	}

	public function update($slug)
	{
		$item = Item::findBySlug($slug);
		if (empty($item))
			return Redirect::to(Fractal::uri('', true))->with('messages', [
				'error' => Fractal::trans('messages.errors.not_found', ['item' => Fractal::transChoiceLower('labels.media_item')]),
			]);

		$result = Item::validateAndSave($item);

		return Redirect::to(Fractal::uri($item->slug.'/edit', true))
			->with('messages', $result['messages'])
			->with('errors', Form::getErrors())
			->withInput();
	}

	public function destroy($id)
	{
		$result = [
			'resultType' => 'Error',
			'message'    => Fractal::trans('messages.errors.general'),
		];

		$item = Item::find($id);
		if (empty($item))
			return $result;

		Activity::log([
			'contentId'   => $item->id,
			'contentType' => 'Media Item',
			'action'      => 'Delete',
			'description' => 'Deleted a Media Item',
			'details'     => 'Title: '.$item->title,
		]);

		$result['resultType'] = "Success";
		$result['message']    = Fractal::trans('messages.success.deleted', ['item' => '<strong>'.$item->getTitle().'</strong>']);

		$item->deleteFiles();

		$item->delete();

		return $result;
	}

	public function getTypesForFileType($fileTypeId = null)
	{
		$mediaTypes = Type::select('id', 'name')->orderBy('name');

		if ($fileTypeId)
			$mediaTypes
				->where('file_type_id', $fileTypeId)
				->orWhereNull('file_type_id');

		return json_encode($mediaTypes->get());
	}

	private function setDefaultImageSizes()
	{
		$imageWidth         = (int) Fractal::getSetting('Default Media Image Width', 0);
		$imageHeight        = (int) Fractal::getSetting('Default Media Image Height', 0);
		$thumbnailImageSize = (int) Fractal::getSetting('Default Image Thumbnail Size', 200);

		if (!$imageWidth)
			$imageWidth = "";

		if (!$imageHeight)
			$imageHeight = "";

		$defaults = [
			'width'            => $imageWidth,
			'height'           => $imageHeight,
			'thumbnail_width'  => $thumbnailImageSize,
			'thumbnail_height' => $thumbnailImageSize,
		];

		Form::setDefaults($defaults);
	}

}