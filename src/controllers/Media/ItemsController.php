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

use Regulus\ActivityLog\Activity;
use \Auth;
use \Form;
use \Format;
use \Site;

class ItemsController extends MediaController {

	public function __construct()
	{
		parent::__construct();

		Fractal::setControllerPath($this);

		Site::set('section', 'Media');
		Site::set('subSection', 'Items');
		Site::set('title', Fractal::lang('labels.mediaItems'));

		//set content type and views location
		Fractal::setContentType('media-item');

		Fractal::setViewsLocation('media.items');

		Fractal::addTrailItem(Fractal::lang('labels.items'), Fractal::getControllerPath());
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
			'label' => Fractal::lang('labels.createItem'),
			'icon'  => 'glyphicon glyphicon-picture',
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
		Site::set('title', Fractal::lang('labels.createItem'));
		Site::set('wysiwyg', true);

		Item::setDefaultsForNew();

		$this->setDefaultImageSizes();

		Form::setErrors();

		Fractal::addButton([
			'label' => Fractal::lang('labels.returnToItemsList'),
			'icon'  => 'glyphicon glyphicon-list',
			'uri'   => Fractal::uri('', true),
		]);

		Fractal::addTrailItem(Fractal::lang('labels.create'), Request::url());

		return View::make(Fractal::view('form'));
	}

	public function store()
	{
		Form::setValidationRules(Item::validationRules());

		$messages = [];
		if (Form::validated()) {
			$hostedExternally = (bool) Input::get('hosted_externally');

			if ($hostedExternally)
				$result = ['error' => false];
			else
				$result = Item::uploadFile();

			if (!$result['error']) {
				$messages['success'] = Fractal::lang('messages.successCreated', ['item' => Fractal::langLowerA('labels.mediaItem')]);

				$path = null;
				$uploadedThumbnail = false;

				if (isset($result['files'])) {
					$fileResult = $result['files']['file'];

					if (isset($result['files']['thumbnail_image']) && !$result['files']['thumbnail_image']['error']) {
						$uploadedThumbnail = true;
						$thumbnailResult   = $result['files']['thumbnail_image'];
					}

					$path = str_replace('uploads/media', '', $fileResult['path']);

					if (substr($path, 0, 1) == "/")
						$path = substr($path, 1);

					if (substr($path, -1) == "/")
						$path = substr($path, 0, (strlen($path) - 1));
				}

				$item = new Item(Input::all());

				$item->title             = ucfirst(trim(Input::get('title')));
				$item->hosted_externally = $hostedExternally;

				if ($hostedExternally) {
					$item->hosted_content_uri = trim(Input::get('hosted_content_uri'));
				} else {
					$item->hosted_content_type = null;
					$item->hosted_content_uri  = null;
					$item->filename            = $fileResult['filename'];
					$item->basename            = $fileResult['basename'];
					$item->extension           = $fileResult['extension'];
					$item->path                = $path;

					if ($fileResult['isImage']) {
						$item->width  = $fileResult['imageDimensions']['w'];
						$item->height = $fileResult['imageDimensions']['h'];

						if (!$uploadedThumbnail) {
							$item->thumbnail           = Form::value('create_thumbnail', 'checkbox');
							$item->thumbnail_extension = null;
							$item->thumbnail_width     = $fileResult['imageDimensions']['tw'];
							$item->thumbnail_height    = $fileResult['imageDimensions']['th'];
						}
					} else {
						$item->width               = null;
						$item->height              = null;
						$item->thumbnail           = false;
						$item->thumbnail_extension = null;
						$item->thumbnail_width     = null;
						$item->thumbnail_height    = null;
					}
				}

				if ($uploadedThumbnail) {
					$item->thumbnail           = true;
					$item->thumbnail_extension = $thumbnailResult['extension'];
					$item->thumbnail_width     = $thumbnailResult['imageDimensions']['tw'];
					$item->thumbnail_height    = $thumbnailResult['imageDimensions']['th'];
				}

				$item->published_at = Input::get('published') ? date('Y-m-d H:i:s', strtotime(Input::get('published_at'))) : null;

				$item->save();

				Activity::log([
					'contentId'   => $item->id,
					'contentType' => 'Item',
					'action'      => 'Create',
					'description' => 'Created a Media Item',
					'details'     => 'Title: '.$item->title,
				]);

				return Redirect::to(Fractal::uri('', true))
					->with('messages', $messages);
			} else {
				$messages['error'] = $result['error'];
			}
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
		$item = Item::findBySlug($slug);
		if (empty($item))
			return Redirect::to(Fractal::uri('media/items'))->with('messages', [
				'error' => Fractal::lang('messages.errorNotFound', ['item' => Fractal::langLower('labels.mediaItem')])
			]);

		Site::set('title', $item->title.' ('.Fractal::lang('labels.mediaItem').')');
		Site::set('titleHeading', Fractal::lang('labels.updateItem').': <strong>'.Format::entities($item->title).'</strong>');
		Site::set('wysiwyg', true);

		$item->setDefaults();

		$this->setDefaultImageSizes();

		Form::setErrors();

		Fractal::addButtons([
			[
				'label' => Fractal::lang('labels.returnToItemsList'),
				'icon'  => 'glyphicon glyphicon-list',
				'uri'   => Fractal::uri('', true),
			],[
				'label' => Fractal::lang('labels.viewItem'),
				'icon'  => 'glyphicon glyphicon-file',
				'url'   => $item->getUrl(),
			]
		]);

		Fractal::addTrailItem(Fractal::lang('labels.update'), Request::url());

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
				'error' => Fractal::lang('messages.errorNotFound', ['item' => Fractal::langLower('labels.mediaItem')]),
			]);

		Form::setValidationRules(Item::validationRules($item->id));

		$messages = [];
		if (Form::validated()) {
			$uploaded          = false;
			$uploadedThumbnail = false;
			$hostedExternally  = (bool) Input::get('hosted_externally');
			$result            = ['error' => false];

			//get original uploaded filename
			$originalFilename  = isset($_FILES['file']['name']) ? $_FILES['file']['name'] : '';
			$originalExtension = strtolower(File::extension($originalFilename));

			//make sure filename is unique and then again remove extension to set basename
			$filename  = Format::unique(Format::slug(Input::get('title')).'.'.File::extension($item->filename), 'media_items', 'filename', $item->id);
			$basename  = str_replace('.'.$originalExtension, '', $filename);
			$extension = $originalExtension;

			if ($originalExtension == "") {
				$filename = null;
				$basename = null;
			}

			$thumbnail = Form::value('create_thumbnail', 'checkbox');

			if ((isset($_FILES['file']['name']) && $_FILES['file']['name'] != "") || (isset($_FILES['thumbnail_image']['name']) && $_FILES['thumbnail_image']['name']))
			{
				$result = Item::uploadFile();

				if (!$result['files']['file']['error']) {
					$uploaded = true;

					$fileResult = $result['files']['file'];
					$filename   = $fileResult['filename'];
					$basename   = $fileResult['basename'];
					$extension  = $fileResult['extension'];

					//delete current thumbnail image if it exists
					if (!$thumbnail && $item->thumbnail && File::exists('uploads/'.$file->getPath(true)))
						File::delete('uploads/'.$file->getPath(true));
				}

				if (!$result['files']['thumbnail_image']['error']) {
					$uploadedThumbnail = true;
					$thumbnailResult   = $result['files']['thumbnail_image'];
				}
			}

			//if file was not uploaded but path or name was changed, move/rename file
			if (!$uploaded && $item->filename != $filename && !is_null($filename)) {
				var_dump($filename); exit;
				//move/rename file
				if (File::exists('uploads/'.$item->getFilePath()))
					File::move('uploads/'.$item->getFilePath(), 'uploads/media/'.$item->path.'/'.$filename);

				//move/rename thumbnail image if it exists
				if (File::exists('uploads/'.$item->getFilePath(true)) && $item->thumbnail)
					File::move('uploads/'.$item->getFilePath(true), 'uploads/media/'.$item->path.'/thumbnails/'.$filename);
			}

			if (!$result['error']) {
				$messages['success'] = Fractal::lang('messages.successUpdated', ['item' => Fractal::langLowerA('labels.mediaItem')]);

				//delete files for item
				if ($hostedExternally && $item->isHostedLocally())
					$item->deleteFiles();

				$item->fill(Input::all());

				$item->title             = ucfirst(trim(Input::get('title')));
				$item->hosted_externally = $hostedExternally;

				if ($hostedExternally) {
					$item->hosted_content_uri = trim(Input::get('hosted_content_uri'));
				} else {
					$item->hosted_content_type = null;
					$item->hosted_content_uri  = null;

					if ($uploaded) {
						$path = str_replace('uploads/media', '', $fileResult['path']);

						if (substr($path, 0, 1) == "/")
							$path = substr($path, 1);

						if (substr($path, -1) == "/")
							$path = substr($path, 0, (strlen($path) - 1));

						$item->filename  = $fileResult['filename'];
						$item->basename  = $fileResult['basename'];
						$item->extension = $fileResult['extension'];
						$item->path      = $path;

						if ($fileResult['isImage']) {
							$item->width  = $fileResult['imageDimensions']['w'];
							$item->height = $fileResult['imageDimensions']['h'];

							if (!$uploadedThumbnail) {
								$item->thumbnail           = Form::value('create_thumbnail', 'checkbox');
								$item->thumbnail_extension = null;
								$item->thumbnail_width     = $fileResult['imageDimensions']['tw'];
								$item->thumbnail_height    = $fileResult['imageDimensions']['th'];
							}
						} else {
							$item->width               = null;
							$item->height              = null;
							$item->thumbnail           = false;
							$item->thumbnail_extension = null;
							$item->thumbnail_width     = null;
							$item->thumbnail_height    = null;
						}
					}

					if ($uploadedThumbnail) {
						$item->thumbnail           = true;
						$item->thumbnail_extension = $thumbnailResult['extension'];
						$item->thumbnail_width     = $thumbnailResult['imageDimensions']['tw'];
						$item->thumbnail_height    = $thumbnailResult['imageDimensions']['th'];
					}
				}

				$item->published_at = Input::get('published') ? date('Y-m-d H:i:s', strtotime(Input::get('published_at'))) : null;

				$item->save();

				Activity::log([
					'contentId'   => $item->id,
					'contentType' => 'Item',
					'action'      => 'Update',
					'description' => 'Updated a Media Item',
					'details'     => 'Title: '.$item->title,
				]);

				/*return Redirect::to(Fractal::uri('media/items'))
					->with('messages', $messages);*/
			} else {
				$error = true;
				foreach ($result['files'] as $file) {
					if ($file['error'])
						$messages['error'] = $file['error'];
					else
						$error = false;
				}

				if (!$error)
					unset($messages['error']);
			}
		} else {
			$messages['error'] = Fractal::lang('messages.errorGeneral');
		}

		return Redirect::to(Fractal::uri($item->slug.'/edit', true))
			->with('messages', $messages)
			->with('errors', Form::getErrors())
			->withInput();
	}

	public function destroy($id)
	{
		$result = [
			'resultType' => 'Error',
			'message'    => Fractal::lang('messages.errorGeneral'),
		];

		$item = Item::find($id);
		if (empty($item))
			return $result;

		Activity::log([
			'contentId'   => $item->id,
			'contentType' => 'Item',
			'action'      => 'Delete',
			'description' => 'Deleted a Media Item',
			'details'     => 'Title: '.$item->title,
		]);

		$result['resultType'] = "Success";
		$result['message']    = Fractal::lang('messages.successDeleted', ['item' => '<strong>'.$item->title.'</strong>']);

		$item->deleteFiles();

		$item->delete();

		return $result;
	}

	public function getTypesForFileType($fileTypeId = null)
	{
		$mediaTypes = Type::select('id', 'name')->orderBy('name');

		if ($fileTypeId)
			$mediaTypes->where('file_type_id', $fileTypeId);

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