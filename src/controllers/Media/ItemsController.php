<?php namespace Regulus\Fractal\Controllers\Media;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

use Fractal;

use Regulus\Fractal\Models\MediaItem;
use Regulus\Fractal\Models\MediaType;
use Regulus\Fractal\Models\FileType;

use Regulus\ActivityLog\Activity;
use \Auth;
use \Form;
use \Format;
use \Site;

class ItemsController extends BaseController {

	public function __construct()
	{
		parent::__construct();

		Site::set('section', 'Content');
		$subSection = "Media";
		Site::setMulti(array('subSection', 'title'), $subSection);

		//set content type and views location
		Fractal::setContentType('media-item', true);

		Fractal::setViewsLocation('media.items');

		Fractal::addTrailItem('Items', 'media/items');
	}

	public function index()
	{
		$data  = Fractal::setupPagination();
		$media = MediaItem::getSearchResults($data);

		Fractal::setContentForPagination($media);

		$data     = Fractal::setPaginationMessage(true);
		$messages = Fractal::getPaginationMessageArray();

		if (!count($media))
			$media = MediaItem::orderBy($data['sortField'], $data['sortOrder'])->paginate($data['itemsPerPage']);

		Fractal::addButton([
			'label' => Lang::get('fractal::labels.createItem'),
			'icon'  => 'glyphicon glyphicon-file',
			'uri'   => 'media/items/create',
		]);

		return View::make(Fractal::view('list'))
			->with('content', $media)
			->with('messages', $messages);
	}

	public function search()
	{
		$data  = Fractal::setupPagination();
		$media = MediaItem::getSearchResults($data);

		Fractal::setContentForPagination($media);

		$data = Fractal::setPaginationMessage();

		if (!count($media))
			$data['content'] = MediaItem::orderBy($data['sortField'], $data['sortOrder'])->paginate($data['itemsPerPage']);

		$data['result']['pages']     = Fractal::getLastPage();
		$data['result']['tableBody'] = Fractal::createTable($data['content'], true);

		return $data['result'];
	}

	public function create()
	{
		Site::set('title', 'Create Media Item');
		Site::set('wysiwyg', true);

		MediaItem::setDefaultsForNew();

		$this->setDefaultImageSizes();

		Form::setErrors();

		Fractal::addButton([
			'label' => Lang::get('fractal::labels.returnToItemsList'),
			'icon'  => 'glyphicon glyphicon-list',
			'uri'   => 'media/items',
		]);

		return View::make(Fractal::view('form'));
	}

	public function store()
	{
		Form::setValidationRules(MediaItem::validationRules());

		$messages = array();
		if (Form::validated()) {
			$hostedExternally = (bool) Input::get('hosted_externally');

			if ($hostedExternally)
				$result = array('error' => false);
			else
				$result = MediaItem::uploadFile();

			if (!$result['error'] && !$result['files']['file']['error']) {
				$messages['success'] = Lang::get('fractal::messages.successCreated', array('item' => Format::a('file')));

				$fileResult = $result['files']['file'];

				if (!$result['files']['thumbnail_image']['error']) {
					$uploadedThumbnail = true;
					$thumbnailResult   = $result['files']['thumbnail_image'];
				}

				$path = str_replace('uploads/media', '', $fileResult['path']);

				if (substr($path, 0, 1) == "/")
					$path = substr($path, 1);

				if (substr($path, -1) == "/")
					$path = substr($path, 0, (strlen($path) - 1));

				$item = new MediaItem(Input::all());

				$item->title             = ucfirst(trim(Input::get('title')));
				$item->hosted_externally = $hostedExternally;

				if ($hostedExternally) {
					$item->hosted_content_uri  = trim($item->hosted_content_uri);
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

				$item->save();

				Activity::log(array(
					'contentId'   => $item->id,
					'contentType' => 'MediaItem',
					'action'      => 'Create',
					'description' => 'Created a Media Item',
					'details'     => 'Title: '.$item->title,
				));

				return Redirect::to(Fractal::uri('media/items'))
					->with('messages', $messages);
			} else {
				$messages['error'] = $result['error'];
			}
		} else {
			$messages['error'] = Lang::get('fractal::messages.errorGeneral');
		}

		return Redirect::to(Fractal::uri('media/items/create'))
			->with('messages', $messages)
			->with('errors', Form::getErrors())
			->withInput();
	}

	public function edit($slug)
	{
		$item = MediaItem::findBySlug($slug);
		if (empty($item))
			return Redirect::to(Fractal::uri('media/items'))
				->with('messages', array('error' => Lang::get('fractal::messages.errorNotFound', array('item' => 'item'))));

		Site::set('title', $item->title.' (Media Item)');
		Site::set('titleHeading', 'Update Media Item: <strong>'.Format::entities($item->title).'</strong>');
		Site::set('wysiwyg', true);

		$item->setDefaults();

		$this->setDefaultImageSizes();

		Form::setErrors();

		Fractal::addButtons([
			[
				'label' => Lang::get('fractal::labels.returnToItemsList'),
				'icon'  => 'glyphicon glyphicon-list',
				'uri'   => 'media/items',
			],[
				'label' => Lang::get('fractal::labels.viewItem'),
				'icon'  => 'glyphicon glyphicon-file',
				'url'   => $item->getUrl(),
			]
		]);

		return View::make(Fractal::view('form'))
			->with('update', true)
			->with('itemUrl', $item->getUrl())
			->with('item', $item);
	}

	public function update($slug)
	{
		$item = MediaItem::findBySlug($slug);
		if (empty($item))
			return Redirect::to(Fractal::uri('media/items'))
				->with('messages', array('error' => Lang::get('fractal::messages.errorNotFound', array('item' => 'item'))));

		Form::setValidationRules(MediaItem::validationRules($item->id));

		$messages = array();
		if (Form::validated()) {
			$uploaded          = false;
			$uploadedThumbnail = false;
			$hostedExternally  = (bool) Input::get('hosted_externally');
			$result            = array('error' => false);

			//get original uploaded filename
			$originalFilename  = isset($_FILES['file']['name']) ? $_FILES['file']['name'] : '';
			$originalExtension = strtolower(File::extension($originalFilename));

			//make sure filename is unique and then again remove extension to set basename
			$filename  = Format::unique(Format::slug(Input::get('title')).'.'.File::extension($item->filename), 'media_items', 'filename', $item->id);
			$basename  = str_replace('.'.$originalExtension, '', $filename);
			$extension = $originalExtension;

			if ((isset($_FILES['file']['name']) && $_FILES['file']['name'] != "") || (isset($_FILES['thumbnail_image']['name']) && $_FILES['thumbnail_image']['name']))
			{
				$result = MediaItem::uploadFile();

				if (!$result['files']['file']['error']) {
					$uploaded = true;

					$fileResult = $result['files']['file'];
					$filename   = $fileResult['filename'];
					$basename   = $fileResult['basename'];
					$extension  = $fileResult['extension'];

					//delete current file
					if (File::exists('uploads/'.$item->getFilePath()))
						File::delete('uploads/'.$item->getFilePath());

					//delete current thumbnail image if it exists
					if (File::exists('uploads/'.$item->getFilePath(true)) && $item->thumbnail)
						File::delete('uploads/'.$item->getFilePath(true));
				}

				if (!$result['files']['thumbnail_image']['error']) {
					$uploadedThumbnail = true;
					$thumbnailResult   = $result['files']['thumbnail_image'];
				}
			}

			//if file was not uploaded but path or name was changed, move/rename file
			if (!$uploaded && $item->filename != $filename) {
				//move/rename file
				if (File::exists('uploads/'.$item->getFilePath()))
					File::move('uploads/'.$item->getFilePath(), 'uploads/media/'.$item->path.'/'.$filename);

				//move/rename thumbnail image if it exists
				if (File::exists('uploads/'.$item->getFilePath(true)) && $item->thumbnail)
					File::move('uploads/'.$item->getFilePath(true), 'uploads/media/'.$item->path.'/thumbnails/'.$filename);
			}

			if (!$result['error']) {
				$messages['success'] = Lang::get('fractal::messages.successUpdated', array('item' => Format::a('file')));

				//delete files for item
				if ($hostedExternally && $item->isHostedLocally())
					$item->deleteFiles();

				$item->fill(Input::all());

				$item->title             = ucfirst(trim(Input::get('title')));
				$item->hosted_externally = $hostedExternally;

				if ($hostedExternally) {
					$item->hosted_content_uri  = trim($item->hosted_content_uri);
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

				$item->save();

				Activity::log(array(
					'contentId'   => $item->id,
					'contentType' => 'MediaItem',
					'action'      => 'Update',
					'description' => 'Updated a Media Item',
					'details'     => 'Title: '.$item->title,
				));

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
			$messages['error'] = Lang::get('fractal::messages.errorGeneral');
		}

		return Redirect::to(Fractal::uri('media/items/'.$item->slug.'/edit'))
			->with('messages', $messages)
			->with('errors', Form::getErrors())
			->withInput();
	}

	public function destroy($id)
	{
		$result = array(
			'resultType' => 'Error',
			'message'    => Lang::get('fractal::messages.errorGeneral'),
		);

		$item = MediaItem::find($id);
		if (empty($item))
			return $result;

		Activity::log(array(
			'contentId'   => $item->id,
			'contentType' => 'MediaItem',
			'action'      => 'Delete',
			'description' => 'Deleted a Media Item',
			'details'     => 'Title: '.$item->title,
		));

		$result['resultType'] = "Success";
		$result['message']    = Lang::get('fractal::messages.successDeleted', array('item' => '<strong>'.$item->title.'</strong>'));

		$item->deleteFiles();

		$item->delete();

		return $result;
	}

	public function getTypesForFileType($fileTypeId = null)
	{
		$mediaTypes = MediaType::select('id', 'name')->orderBy('name');

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

		$defaults = array(
			'width'            => $imageWidth,
			'height'           => $imageHeight,
			'thumbnail_width'  => $thumbnailImageSize,
			'thumbnail_height' => $thumbnailImageSize,
		);
		Form::setDefaults($defaults);
	}

}