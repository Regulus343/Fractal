<?php namespace Regulus\Fractal;

use \BaseController;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL;

use Aquanode\Elemental\Elemental as HTML;
use Aquanode\Formation\Formation as Form;
use Aquanode\Upstream\Upstream;
use Regulus\ActivityLog\Activity;
use Regulus\TetraText\TetraText as Format;
use Regulus\SolidSite\SolidSite as Site;

class FilesController extends BaseController {

	public function __construct()
	{
		Site::set('section', 'Content');
		$subSection = "Files";
		Site::setMulti(array('subSection', 'title'), $subSection);

		Fractal::setViewsLocation('files');
	}

	public function index()
	{
		$data = Fractal::setupPagination('Files');

		$files = ContentFile::orderBy('id');
		if ($data['terms'] != "") {
			$files->where(function($query) use ($data) {
				$query
					->where('name', 'like', $data['likeTerms'])
					->orWhere('filename', 'like', $data['likeTerms'])
					->orWhere('type', 'like', $data['likeTerms']);
			});
		}
		$files = $files->paginate($data['itemsPerPage']);

		Fractal::addContentForPagination($files);

		$data = Fractal::setPaginationMessage();
		$messages['success'] = $data['result']['message'];

		$defaults = array(
			'search' => $data['terms']
		);
		Form::setDefaults($defaults);

		return View::make(Fractal::view('list'))
			->with('files', $files)
			->with('contentType', 'files')
			->with('page', $files->getCurrentPage())
			->with('lastPage', $files->getLastPage())
			->with('messages', $messages);
	}

	public function search()
	{
		$data = Fractal::setupPagination('Files');

		$files = ContentFile::orderBy('id');
		if ($data['terms'] != "") {
			$files->where(function($query) use ($data) {
				$query
					->where('name', 'like', $data['likeTerms'])
					->orWhere('filename', 'like', $data['likeTerms'])
					->orWhere('type', 'like', $data['likeTerms']);
			});
		}
		$files = $files->paginate($data['itemsPerPage']);

		Fractal::addContentForPagination($files);

		if (count($files)) {
			$data = Fractal::setPaginationMessage();
		} else {
			$data['content'] = User::orderBy('id')->paginate($data['itemsPerPage']);
			if ($terms == "") $result['message'] = Lang::get('fractal::messages.searchNoTerms');
		}

		$data['result']['table'] = HTML::table(Config::get('fractal::tables.files'), $data['content']);

		return $data['result'];
	}

	public function create()
	{
		Site::set('title', 'Create File');

		$thumbnailSize = Fractal::getSetting('Default Image Thumbnail Size', 120);
		$defaults = array(
			'thumbnail_width'  => $thumbnailSize,
			'thumbnail_height' => $thumbnailSize,
		);
		Form::setDefaults($defaults);

		return View::make(Fractal::view('form'))->with('update', false);
	}

	public function store()
	{
		Site::set('title', 'Create File');

		Form::setValidationRules(ContentFile::validationRules());

		$messages = array();
		if (Form::validated()) {
			//get original uploaded filename
			$originalFilename  = isset($_FILES['file']['name']) ? $_FILES['file']['name'] : '';
			$originalExtension = File::extension($originalFilename);

			//make sure filename is unique and then again remove extension to set basename
			$basename = str_replace('-'.$originalExtension, '', Format::unique(Format::slug(Input::get('name')).'.'.$originalExtension, 'content_files', 'filename', false, true));

			$config = array(
				'path'             => 'uploads',
				'fields'           => 'file',
				'filename'         => $basename,
				'createDirectory'  => true,
				'overwrite'        => true,
				'maxFileSize'      => '5MB',
			);

			//set path
			$path = Input::get('path') ? Input::get('path') : '';
			if ($path != "")
				$config['path'] .= '/'.$path;

			//set image resize settings
			$width           = 0;
			$height          = 0;
			$thumbnailWidth  = 0;
			$thumbnailHeight = 0;
			if (Input::get('type') == "Image") {
				$width  = Input::get('width');
				$height = Input::get('height');

				$defaultThumbnailSize = Fractal::getSetting('Default Image Thumbnail Size', 120);
				if ($width != "" && $height != "" && $width > 0 && $height > 0) {
					$config['imgResize']        = true;
					$config['imgResizeQuality'] = Fractal::getSetting('Image Resize Quality', 60);
					$config['imgCrop']          = Form::value('crop', 'checkbox');
				}

				$config['imgThumb']         = Form::value('create_thumbnail', 'checkbox');
				$config['imgDimensions']    = array(
					'w'  => (int) $width,
					'h'  => (int) $height,
					'tw' => (int) Input::get('thumbnail_width') > 0  ? (int) Input::get('thumbnail_width')  : $defaultThumbnailSize,
					'th' => (int) Input::get('thumbnail_height') > 0 ? (int) Input::get('thumbnail_height') : $defaultThumbnailSize,
				);
			}

			$upstream = Upstream::make($config);
			$result   = $upstream->upload();

			if (!$result['error']) {
				$messages['success'] = Lang::get('fractal::messages.successCreated', array('item' => Format::a('file')));

				if (Input::get('type') == "Image") {
					$size   = getimagesize($config['path'].'/'.$result['filename']);
					$width  = $size[0];
					$height = $size[1];

					if (Form::value('create_thumbnail', 'checkbox') && File::exists($config['path'].'/thumbnails/'.$result['filename'])) {
						$thumbnailSize   = getimagesize($config['path'].'/thumbnails/'.$result['filename']);
						$thumbnailWidth  = $thumbnailSize[0];
						$thumbnailHeight = $thumbnailSize[1];
					}
				}

				$file = new ContentFile;
				$file->name             = ucfirst(trim(Input::get('name')));
				$file->filename         = $result['filename'];
				$file->basename         = $basename;
				$file->extension        = File::extension($result['filename']);
				$file->path             = $path;
				$file->type             = Input::get('type');
				$file->width            = $width;
				$file->height           = $height;
				$file->thumbnail        = Form::value('create_thumbnail', 'checkbox');
				$file->thumbnail_width  = $thumbnailWidth;
				$file->thumbnail_height = $thumbnailHeight;
				$file->save();

				Activity::log(array(
					'contentID'   => $file->id,
					'contentType' => 'ContentFile',
					'description' => 'Created a File',
					'details'     => 'Filename: '.$file->filename,
				));

				return Redirect::to(Fractal::uri('files'))
					->with('messages', $messages);
			} else {
				var_dump($result); exit;
				$messages['error'] = $result['error'];
			}
		} else {
			$messages['error'] = Lang::get('fractal::messages.errorGeneral');
		}

		return View::make(Fractal::view('form'))
			->with('messages', $messages);
	}

	public function edit($id)
	{
		$file = ContentFile::find($id);
		if (empty($file))
			return Redirect::to(Fractal::uri('pages'))
				->with('messages', array('error' => Lang::get('fractal::messages.errorNotFound', array('item' => 'file'))));

		Site::set('title', $file->name.' (File)');
		Site::set('titleHeading', 'Update File: <strong>'.Format::entities($file->name).'</strong>');

		Form::setDefaults($file);
		Form::setErrors();

		return View::make(Fractal::view('form'))->with('update', true);
	}

	public function update($id)
	{
		$file = ContentFile::find($id);
		if (empty($file))
			return Redirect::to(Fractal::uri('pages'))
				->with('messages', array('error' => Lang::get('fractal::messages.errorNotFound', array('item' => 'file'))));

		Site::set('title', $file->name.' (File)');
		Site::set('titleHeading', 'Update File: <strong>'.Format::entities($file->name).'</strong>');

		Form::setValidationRules(ContentFile::validationRules($id));

		$messages = array();
		if (Form::validated()) {
			$uploaded = false;
			$result   = array('error' => false);
			$path     = Input::get('path') ? Input::get('path') : '';

			if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != "") {
				//get original uploaded filename
				$originalFilename  = isset($_FILES['file']['name']) ? $_FILES['file']['name'] : '';
				$originalExtension = File::extension($originalFilename);

				//make sure filename is unique and then again remove extension to set basename
				$basename = str_replace('-'.$originalExtension, '', Format::unique(Format::slug(Input::get('name')).'.'.$originalExtension, 'content_files', 'filename', $id, true));
				$filename = $basename.'.'.$originalExtension;

				$tempBasename = md5(rand(1000, 9999999));
				$tempFilename = $tempBasename.'.'.$originalExtension;

				$config = array(
					'path'             => 'uploads',
					'fields'           => 'file',
					'filename'         => $tempBasename,
					'createDirectory'  => true,
					'overwrite'        => true,
					'maxFileSize'      => '5MB',
				);

				//set path
				if ($path != "")
					$config['path'] .= '/'.$path;

				//set image resize settings
				$width  = 0;
				$height = 0;
				if (Input::get('type') == "Image") {
					$width  = Input::get('width');
					$height = Input::get('height');

					$defaultThumbnailSize = Fractal::getSetting('Default Image Thumbnail Size', 120);
					if ($width != "" && $height != "" && $width > 0 && $height > 0) {
						$config['imgResize']        = true;
						$config['imgResizeQuality'] = Fractal::getSetting('Image Resize Quality', 60);
						$config['imgCrop']          = Form::value('crop', 'checkbox');
					}

					$config['imgThumb']         = Form::value('create_thumbnail', 'checkbox');
					$config['imgDimensions']    = array(
						'w'  => (int) $width,
						'h'  => (int) $height,
						'tw' => (int) Input::get('thumbnail_width') > 0  ? (int) Input::get('thumbnail_width')  : $defaultThumbnailSize,
						'th' => (int) Input::get('thumbnail_height') > 0 ? (int) Input::get('thumbnail_height') : $defaultThumbnailSize,
					);
				}

				$upstream = Upstream::make($config);
				$result   = $upstream->upload();

				if (!$result['error']) {
					$uploaded = true;

					//delete current file
					if (File::exists('uploads/'.$file->getPath()))
						File::delete('uploads/'.$file->getPath());

					//delete current thumbnail image if it exists
					if (File::exists('uploads/'.$file->getPath(true)) && $file->thumbnail)
						File::delete('uploads/'.$file->getPath(true));

					//rename file
					if (File::exists($config['path'].'/'.$tempFilename))
						File::move($config['path'].'/'.$tempFilename, $config['path'].'/'.$filename);

					//rename thumbnail image if it exists
					if (File::exists($config['path'].'/thumbnails/'.$tempFilename) && Form::value('create_thumbnail', 'checkbox'))
						File::move($config['path'].'/thumbnails/'.$tempFilename, $config['path'].'/thumbnails/'.$filename);
				}
			} else {
				//make sure filename is unique and then again remove extension to set basename
				$basename = str_replace('-'.File::extension($file->filename), '', Format::uniqueSlug(Format::slug(Input::get('name')).'.'.File::extension($file->filename), 'content_files', $id, false, 'filename'));
				$filename = $basename.'.'.File::extension($file->filename);
			}

			//if file was not uploaded but path or name was changed, move/rename file
			if (!$uploaded && ($file->path != $path || $file->filename != $filename)) {
				//move/rename file
				if (File::exists('uploads/'.$file->path.'/'.$file->filename))
					File::move('uploads/'.$file->path.'/'.$file->filename, 'uploads/'.$path.'/'.$filename);

				//move/rename thumbnail image if it exists
				if (File::exists('uploads/'.$file->path.'/thumbnails/'.$file->filename) && $file->thumbnail)
					File::move('uploads/'.$file->path.'/thumbnails/'.$file->filename, 'uploads/'.$path.'/thumbnails/'.$filename);
			}

			if (!$result['error']) {
				$messages['success'] = Lang::get('fractal::messages.successCreated', array('item' => Format::a('file')));

				$file->name      = ucfirst(trim(Input::get('name')));
				$file->filename  = $filename;
				$file->basename  = $basename;
				$file->extension = File::extension($filename);
				$file->path      = $path;

				if ($uploaded) {
					$size   = getimagesize($config['path'].'/'.$filename);
					$width  = $size[0];
					$height = $size[1];

					$thumbnailWidth  = 0;
					$thumbnailHeight = 0;
					if (Form::value('create_thumbnail', 'checkbox') && File::exists($config['path'].'/thumbnails/'.$filename)) {
						$thumbnailSize   = getimagesize($config['path'].'/thumbnails/'.$filename);
						$thumbnailWidth  = $thumbnailSize[0];
						$thumbnailHeight = $thumbnailSize[1];
					}

					$file->type             = Input::get('type');
					$file->width            = $width;
					$file->height           = $height;
					$file->thumbnail        = Form::value('create_thumbnail', 'checkbox');
					$file->thumbnail_width  = $thumbnailWidth;
					$file->thumbnail_height = $thumbnailHeight;
				}

				$file->save();

				Activity::log(array(
					'contentID'   => $file->id,
					'contentType' => 'ContentFile',
					'description' => 'Created a File',
					'details'     => 'Filename: '.$file->filename,
				));

				return Redirect::to(Fractal::uri('files'))
					->with('messages', $messages);
			} else {
				$messages['error'] = $result['error'];
			}
		} else {
			$messages['error'] = Lang::get('fractal::messages.errorGeneral');
		}

		return View::make(Fractal::view('form'))
			->with('messages', $messages);
	}

	public function destroy($id)
	{
		$result = array(
			'resultType' => 'Error',
			'message'    => Lang::get('fractal::messages.errorGeneral'),
		);

		$file = ContentFile::find($id);
		if (empty($file))
			return $result;

		Activity::log(array(
			'contentID'   => $file->id,
			'contentType' => 'ContentFile',
			'description' => 'Deleted a File',
			'details'     => 'Filename: '.$file->filename,
		));

		$result['resultType'] = "Success";
		$result['message']    = Lang::get('fractal::messages.successDeleted', array('item' => '<strong>'.$file->name.'</strong>'));

		//delete file
		if (File::exists('uploads/'.$file->getPath()))
			File::delete('uploads/'.$file->getPath());

		//delete thumbnail image if it exists
		if (File::exists('uploads/'.$file->getPath(true)) && $file->thumbnail)
			File::delete('uploads/'.$file->getPath(true));

		$file->delete();

		return $result;
	}

}