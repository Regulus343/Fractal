<?php namespace Regulus\Fractal\Models\Content;

use Regulus\Formation\Models\Base;

use Fractal;

use Illuminate\Support\Facades\File as FileHelper;
use Illuminate\Support\Facades\Input;

use Regulus\Formation\Facade as Form;
use Regulus\TetraText\Facade as Format;
use Regulus\SolidSite\Facade as Site;
use Regulus\Upstream\Facade as Upstream;

class File extends Base {

	/**
	 * The database table used by the model.
	 *
	 * @var    string
	 */
	protected $table = 'content_files';

	/**
	 * The fillable fields for the model.
	 *
	 * @var    array
	 */
	protected $fillable = [
		'type_id',
		'image_template_id',
		'name',
		'filename',
		'basename',
		'extension',
		'path',
		'width',
		'height',
		'thumbnail',
		'thumbnail_width',
		'thumbnail_height',
		'user_id',
	];

	/**
	 * Get the validation rules used by the model.
	 *
	 * @param  boolean  $id
	 * @return string
	 */
	public static function validationRules($id = null)
	{
		$rules = [
			'name' => ['required'],
		];

		if (!$id)
			$rules['file'] = ['required'];

		return $rules;
	}

	/**
	 * The file type of the file.
	 *
	 * @return User
	 */
	public function type()
	{
		return $this->belongsTo('Regulus\Fractal\Models\Content\FileType', 'type_id');
	}

	/**
	 * The creator of the file.
	 *
	 * @return User
	 */
	public function creator()
	{
		return $this->belongsTo(config('auth.model'), 'user_id');
	}

	/**
	 * The file type of the file.
	 *
	 * @return string
	 */
	public function getType()
	{
		if ($this->type)
			return $this->type->name;

		return "Unknown (.".$this->extension.")";
	}

	/**
	 * Get the path of the file.
	 *
	 * @param  boolean  $thumbnail
	 * @return string
	 */
	public function getPath($thumbnail = false)
	{
		$path = 'files/';

		if ($this->path != "")
			$path = $path.$this->path.'/';

		$path .= ($thumbnail && $this->thumbnail ? 'thumbnails/' : '').$this->filename;

		return $path;
	}

	/**
	 * Get the URL of the file.
	 *
	 * @param  boolean  $thumbnail
	 * @return string
	 */
	public function getUrl($thumbnail = false)
	{
		return Site::uploadedFile($this->getPath($thumbnail));
	}

	/**
	 * Get the image URL or a placeholder image URL for the file.
	 *
	 * @param  boolean  $thumbnail
	 * @return string
	 */
	public function getImageUrl($thumbnail = false)
	{
		if ($this->getType() == "Image")
			return $this->getUrl($thumbnail);
		else
			return Fractal::getImageUrlFromConfig('cms.placeholder_image');
	}

	/**
	 * Get the image or a placeholder image for the file.
	 *
	 * @param  boolean  $thumbnail
	 * @return string
	 */
	public function getImage($thumbnail = false)
	{
		return '<a href="'.$this->getUrl().'" target="_blank"><img src="'.$this->getImageUrl($thumbnail).'" alt="'.$this->name.'" title="'.$this->name.'" /></a>';
	}

	/**
	 * Get the thumbnail image or a placeholder image for the file.
	 *
	 * @return string
	 */
	public function getThumbnailImage()
	{
		return $this->getImage(true);
	}

	/**
	 * Get the image dimensions of the file and the thumbnail if it exists.
	 *
	 * @param  boolean  $thumbnail
	 * @return string
	 */
	public function getImageDimensions($thumbnail = false)
	{
		if ($this->getType() != "Image")
			return "&ndash;";

		$dimensions = '<div>'.$this->width.' x '.$this->height.'</div>';
		if ($this->thumbnail)
			$dimensions .= '<div>'.$this->thumbnail_width.' x '.$this->thumbnail_height.'</div>';

		return $dimensions;
	}

	/**
	 * Get the last updated date/time.
	 *
	 * @param  mixed    $dateFormat
	 * @return string
	 */
	public function getLastUpdatedDateTime($dateFormat = false)
	{
		if (!$dateFormat)
			$dateFormat = config('format.defaults.datetime');

		return $this->updated_at != "0000-00-00" ? date($dateFormat, strtotime($this->updated_at)) : date($dateFormat, strtotime($this->created_at));
	}

	/**
	 * Get files for a specific extension.
	 *
	 * @param  string   $extension
	 * @return File
	 */
	public static function getForExtension($extension)
	{
		return static::where('extension', $extension)->orderBy('name')->get();
	}

	/**
	 * Get file search results.
	 *
	 * @param  array    $searchData
	 * @return File
	 */
	public static function getSearchResults($searchData)
	{
		$files = static::orderBy($searchData['sortField'], $searchData['sortOrder']);

		if ($searchData['sortField'] != "id")
			$files->orderBy('id', 'asc');

		if ($searchData['terms'] != "")
			$files->where(function($query) use ($searchData) {
				$query
					->where('name', 'like', $searchData['likeTerms'])
					->orWhere('filename', 'like', $searchData['likeTerms']);
			});

		$filters = $searchData['filters'];
		if (!empty($filters))
		{
			$allowedFilters = [
				'type_id',
			];

			foreach ($allowedFilters as $allowedFilter)
			{
				if (isset($filters[$allowedFilter]) && $filters[$allowedFilter] && $filters[$allowedFilter] != "")
					$files->where($allowedFilter, $filters[$allowedFilter]);
			}
		}

		Fractal::setRequestedPage();

		return $files->paginate($searchData['itemsPerPage']);
	}

	/**
	 * Upload a file.
	 *
	 * @param  mixed    $id
	 * @return array
	 */
	public static function uploadFile($id = null) {
		//get original uploaded filename
		$originalFilename  = isset($_FILES['file']['name']) ? $_FILES['file']['name'] : '';
		$originalExtension = strtolower(FileHelper::extension($originalFilename));

		//make sure filename is unique and then again remove extension to set basename
		$uniqueFilename = Format::unique(Format::slug(Input::get('name')).'.'.$originalExtension, 'content_files', 'filename', $id, true);
		$basename       = str_replace('.'.$originalExtension, '', $uniqueFilename);

		$path = "uploads/files";

		$fileType = FileType::find(Input::get('type_id'));
		if (!empty($fileType))
			$path .= '/'.$fileType->slug;

		$config = [
			'path'            => $path,
			'fields'          => 'file',
			'filename'        => $basename,
			'createDirectory' => true,
			'overwrite'       => true,
			'maxFileSize'     => '8MB',
		];

		//set image resize settings
		if (!empty($fileType) && $fileType->name == "Image") {
			$width           = Input::get('width');
			$height          = Input::get('height');
			$thumbnailWidth  = 0;
			$thumbnailHeight = 0;

			$defaultThumbnailSize = Fractal::getSetting('Default Image Thumbnail Size', 200);
			if ($width != "" && $height != "" && $width > 0 && $height > 0) {
				$config['imageResize']        = true;
				$config['imageResizeQuality'] = Fractal::getSetting('Image Resize Quality', 60);
				$config['imageCrop']          = Form::value('crop', 'checkbox');
			}

			$config['imageThumb']      = Form::value('create_thumbnail', 'checkbox');
			$config['imageDimensions'] = [
				'w'  => (int) $width,
				'h'  => (int) $height,
				'tw' => (int) Input::get('thumbnail_width') > 0  ? (int) Input::get('thumbnail_width')  : $defaultThumbnailSize,
				'th' => (int) Input::get('thumbnail_height') > 0 ? (int) Input::get('thumbnail_height') : $defaultThumbnailSize,
			];
		}

		$upstream = Upstream::make($config);

		return $upstream->upload();
	}

}