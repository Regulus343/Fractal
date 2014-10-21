<?php namespace Regulus\Fractal\Controllers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

use Fractal;

use Regulus\Fractal\Models\ContentFile;
use Regulus\Fractal\Models\FileType;

use Regulus\ActivityLog\Activity;
use \Form;
use \Format;
use \Site;

class FilesController extends BaseController {

	public function __construct()
	{
		parent::__construct();

		Fractal::setControllerPath($this);

		Site::set('section', 'Content');
		Site::set('subSection', 'Files');
		Site::set('title', Fractal::lang('labels.files'));

		//set content type and views location
		Fractal::setContentType('file', true);

		Fractal::addTrailItem('Files', Fractal::getControllerPath());
	}

	public function index()
	{
		$data  = Fractal::setupPagination('Files');
		$files = ContentFile::getSearchResults($data);

		Fractal::setContentForPagination($files);

		$data     = Fractal::setPaginationMessage(true);
		$messages = Fractal::getPaginationMessageArray();

		if (!count($files))
			$files = ContentFile::orderBy($data['sortField'], $data['sortOrder'])->paginate($data['itemsPerPage']);

		Fractal::addButton([
			'label' => Fractal::lang('labels.uploadFile'),
			'icon'  => 'glyphicon glyphicon-file',
			'uri'   => Fractal::uri('create', true),
		]);

		return View::make(Fractal::view('list'))
			->with('content', $files)
			->with('messages', $messages);
	}

	public function search()
	{
		$data  = Fractal::setupPagination('Files');
		$files = ContentFile::getSearchResults($data);

		Fractal::setContentForPagination($files);

		$data = Fractal::setPaginationMessage();

		if (!count($files))
			$data['content'] = ContentFile::orderBy($data['sortField'], $data['sortOrder'])->paginate($data['itemsPerPage']);

		$data['result']['pages']     = Fractal::getLastPage();
		$data['result']['tableBody'] = Fractal::createTable($data['content'], true);

		return $data['result'];
	}

	public function create()
	{
		Site::set('title', 'Upload File');

		$this->setDefaultImageSize();

		Form::setErrors();

		Fractal::addButton([
			'label' => Fractal::lang('labels.returnToFilesList'),
			'icon'  => 'glyphicon glyphicon-list',
			'uri'   => Fractal::uri('', true),
		]);

		return View::make(Fractal::view('form'))->with('update', false);
	}

	public function store()
	{
		Form::setValidationRules(ContentFile::validationRules());

		$messages = [];
		if (Form::validated()) {
			$result = ContentFile::uploadFile();

			if (!$result['error']) {
				$messages['success'] = Fractal::lang('messages.successCreated', ['item' => Fractal::langLowerA('labels.file')]);

				$fileResult = $result['files']['file'];

				$path = str_replace('uploads/files', '', $fileResult['path']);

				if (substr($path, 0, 1) == "/")
					$path = substr($path, 1);

				if (substr($path, -1) == "/")
					$path = substr($path, 0, (strlen($path) - 1));

				$file = new ContentFile;

				$file->type_id          = Input::get('type_id');
				$file->name             = ucfirst(trim(Input::get('name')));
				$file->filename         = $fileResult['filename'];
				$file->basename         = $fileResult['basename'];
				$file->extension        = $fileResult['extension'];
				$file->path             = $path;
				$file->width            = $fileResult['imageDimensions']['w'];
				$file->height           = $fileResult['imageDimensions']['h'];
				$file->thumbnail        = Form::value('create_thumbnail', 'checkbox');
				$file->thumbnail_width  = $fileResult['imageDimensions']['tw'];
				$file->thumbnail_height = $fileResult['imageDimensions']['th'];
				$file->save();

				Activity::log([
					'contentId'   => $file->id,
					'contentType' => 'ContentFile',
					'action'      => 'Create',
					'description' => 'Created a File',
					'details'     => 'Filename: '.$file->filename,
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

	public function edit($id)
	{
		$file = ContentFile::find($id);
		if (empty($file))
			return Redirect::to(Fractal::uri('', true))->with('messages', [
				'error' => Fractal::lang('messages.errorNotFound', ['item' => Fractal::langLower('labels.file')])
			]);

		Site::set('title', $file->name.' (File)');
		Site::set('titleHeading', 'Update File: <strong>'.Format::entities($file->name).'</strong>');

		Form::setDefaults($file);

		$this->setDefaultImageSize();

		Form::setErrors();

		Fractal::addButton([
			'label' => Fractal::lang('labels.returnToFilesList'),
			'icon'  => 'glyphicon glyphicon-list',
			'uri'   => 'files',
		]);

		return View::make(Fractal::view('form'))->with('update', true);
	}

	public function update($id)
	{
		$file = ContentFile::find($id);
		if (empty($file))
			return Redirect::to(Fractal::uri('', true))->with('messages', [
				'error' => Fractal::lang('messages.errorNotFound', ['item' => Fractal::langLower('labels.file')])
			]);

		Form::setValidationRules(ContentFile::validationRules($id));

		$messages = [];
		if (Form::validated()) {
			$uploaded = false;
			$result   = ['error' => false];

			//get original uploaded filename
			$originalFilename  = isset($_FILES['file']['name']) ? $_FILES['file']['name'] : '';
			$originalExtension = strtolower(File::extension($originalFilename));

			//make sure filename is unique and then again remove extension to set basename
			$filename  = Format::unique(Format::slug(Input::get('name')).'.'.File::extension($file->filename), 'content_files', 'filename', $id);
			$basename  = str_replace('.'.$originalExtension, '', $filename);
			$extension = $originalExtension;
			$thumbnail = Form::value('create_thumbnail', 'checkbox');

			if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != "")
			{
				$result = ContentFile::uploadFile($id);

				if (!$result['error']) {
					$uploaded   = true;
					$fileResult = $result['files']['file'];
					$filename   = $fileResult['filename'];
					$basename   = $fileResult['basename'];
					$extension  = $fileResult['extension'];

					//delete current thumbnail image if it exists
					if (!$thumbnail && $file->thumbnail && File::exists('uploads/'.$file->getPath(true)))
						File::delete('uploads/'.$file->getPath(true));
				}
			}

			//if file was not uploaded but path or name was changed, move/rename file
			if (!$uploaded && $file->filename != $filename) {
				//move/rename file
				if (File::exists('uploads/'.$file->getPath()))
					File::move('uploads/'.$file->getPath(), 'uploads/files/'.$file->path.'/'.$filename);

				//move/rename thumbnail image if it exists
				if (File::exists('uploads/'.$file->getPath(true)) && $file->thumbnail)
					File::move('uploads/'.$file->getPath(true), 'uploads/files/'.$file->path.'/thumbnails/'.$filename);
			}

			if (!$result['error']) {
				$messages['success'] = Fractal::lang('messages.successUpdated', ['item' => Fractal::langLowerA('labels.file')]);

				$file->name      = ucfirst(trim(Input::get('name')));
				$file->filename  = $filename;
				$file->basename  = $basename;
				$file->extension = File::extension($filename);

				if ($uploaded) {
					$path = str_replace('uploads/files', '', $fileResult['path']);

					if (substr($path, 0, 1) == "/")
						$path = substr($path, 1);

					if (substr($path, -1) == "/")
						$path = substr($path, 0, (strlen($path) - 1));

					$file->type_id   = Input::get('type_id');
					$file->path      = $path;
					$file->width     = $fileResult['imageDimensions']['w'];
					$file->height    = $fileResult['imageDimensions']['h'];
					$file->thumbnail = $thumbnail;

					if ($thumbnail) {
						$file->thumbnail_width  = $fileResult['imageDimensions']['tw'];
						$file->thumbnail_height = $fileResult['imageDimensions']['th'];
					} else {
						$file->thumbnail_width  = null;
						$file->thumbnail_height = null;
					}
				}

				$file->save();

				Activity::log([
					'contentId'   => $file->id,
					'contentType' => 'ContentFile',
					'action'      => 'Update',
					'description' => 'Updated a File',
					'details'     => 'Filename: '.$file->filename,
				]);

				return Redirect::to(Fractal::uri('', true))
					->with('messages', $messages);
			} else {
				$messages['error'] = $result['error'];
			}
		} else {
			$messages['error'] = Fractal::lang('messages.errorGeneral');
		}

		return Redirect::to(Fractal::uri($id.'/edit', true))
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

		$file = ContentFile::find($id);
		if (empty($file))
			return $result;

		Activity::log([
			'contentId'   => $file->id,
			'contentType' => 'ContentFile',
			'action'      => 'Delete',
			'description' => 'Deleted a File',
			'details'     => 'Filename: '.$file->filename,
		]);

		$result['resultType'] = "Success";
		$result['message']    = Fractal::lang('messages.successDeleted', ['item' => '<strong>'.$file->name.'</strong>']);

		//delete file
		if (File::exists('uploads/'.$file->getPath()))
			File::delete('uploads/'.$file->getPath());

		//delete thumbnail image if it exists
		if (File::exists('uploads/'.$file->getPath(true)) && $file->thumbnail)
			File::delete('uploads/'.$file->getPath(true));

		$file->delete();

		return $result;
	}

	private function setDefaultImageSize()
	{
		$thumbnailSize = (int) Fractal::getSetting('Default Image Thumbnail Size', 200);

		$defaults = [
			'thumbnail_width'  => $thumbnailSize,
			'thumbnail_height' => $thumbnailSize,
		];
		Form::setDefaults($defaults);
	}

}