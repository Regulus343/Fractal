<?php namespace Regulus\Fractal\Controllers;

use \BaseController;

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
		Site::set('section', 'Content');
		$subSection = "Files";
		Site::setMulti(array('subSection', 'title'), $subSection);

		//set content type and views location
		Fractal::setContentType('file', true);
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

		return View::make(Fractal::view('form'))->with('update', false);
	}

	public function store()
	{
		Form::setValidationRules(ContentFile::validationRules());

		$messages = array();
		if (Form::validated()) {
			$result = ContentFile::uploadFile();

			if (!$result['error']) {
				$messages['success'] = Lang::get('fractal::messages.successCreated', array('item' => Format::a('file')));

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

				Activity::log(array(
					'contentId'   => $file->id,
					'contentType' => 'ContentFile',
					'action'      => 'Create',
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

		return Redirect::to(Fractal::uri('files/create'))
			->with('messages', $messages)
			->with('errors', Form::getErrors())
			->withInput();
	}

	public function edit($id)
	{
		$file = ContentFile::find($id);
		if (empty($file))
			return Redirect::to(Fractal::uri('files'))
				->with('messages', array('error' => Lang::get('fractal::messages.errorNotFound', array('item' => 'file'))));

		Site::set('title', $file->name.' (File)');
		Site::set('titleHeading', 'Update File: <strong>'.Format::entities($file->name).'</strong>');

		Form::setDefaults($file);

		$this->setDefaultImageSize();

		Form::setErrors();

		return View::make(Fractal::view('form'))->with('update', true);
	}

	public function update($id)
	{
		$file = ContentFile::find($id);
		if (empty($file))
			return Redirect::to(Fractal::uri('files'))
				->with('messages', array('error' => Lang::get('fractal::messages.errorNotFound', array('item' => 'file'))));

		Form::setValidationRules(ContentFile::validationRules($id));

		$messages = array();
		if (Form::validated()) {
			$uploaded = false;
			$result   = array('error' => false);

			//get original uploaded filename
			$originalFilename  = isset($_FILES['file']['name']) ? $_FILES['file']['name'] : '';
			$originalExtension = strtolower(File::extension($originalFilename));

			//make sure filename is unique and then again remove extension to set basename
			$filename  = Format::unique(Format::slug(Input::get('name')).'.'.File::extension($file->filename), 'content_files', 'filename', $id);
			$basename  = str_replace('.'.$originalExtension, '', $filename);
			$extension = $originalExtension;

			if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != "")
			{
				$result = ContentFile::uploadFile();

				if (!$result['error']) {
					$uploaded   = true;
					$fileResult = $result['files']['file'];
					$filename   = $fileResult['filename'];
					$basename   = $fileResult['basename'];
					$extension  = $fileResult['extension'];

					//delete current file
					if (File::exists('uploads/'.$file->getPath()))
						File::delete('uploads/'.$file->getPath());

					//delete current thumbnail image if it exists
					if (File::exists('uploads/'.$file->getPath(true)) && $file->thumbnail)
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
				$messages['success'] = Lang::get('fractal::messages.successUpdated', array('item' => Format::a('file')));

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

					$file->type_id          = Input::get('type_id');
					$file->path             = $path;
					$file->width            = $fileResult['imageDimensions']['w'];
					$file->height           = $fileResult['imageDimensions']['h'];
					$file->thumbnail        = Form::value('create_thumbnail', 'checkbox');
					$file->thumbnail_width  = $fileResult['imageDimensions']['tw'];
					$file->thumbnail_height = $fileResult['imageDimensions']['th'];
				}

				$file->save();

				Activity::log(array(
					'contentId'   => $file->id,
					'contentType' => 'ContentFile',
					'action'      => 'Update',
					'description' => 'Updated a File',
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

		return Redirect::to(Fractal::uri('files/'.$id.'/edit'))
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

		$file = ContentFile::find($id);
		if (empty($file))
			return $result;

		Activity::log(array(
			'contentId'   => $file->id,
			'contentType' => 'ContentFile',
			'action'      => 'Delete',
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

	private function setDefaultImageSize()
	{
		$thumbnailSize = (int) Fractal::getSetting('Default Image Thumbnail Size', 200);

		$defaults = array(
			'thumbnail_width'  => $thumbnailSize,
			'thumbnail_height' => $thumbnailSize,
		);
		Form::setDefaults($defaults);
	}

}