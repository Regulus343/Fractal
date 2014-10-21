<?php namespace Regulus\Fractal\Models;

use Regulus\Formation\BaseModel;

use Fractal;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;

use \Form;
use \Format;
use \Site;
use \Upstream;

use Regulus\Fractal\Traits\PublishingTrait;

class MediaItem extends BaseModel {

	/**
	 * The database table used by the model.
	 *
	 * @var    string
	 */
	protected $table = 'media_items';

	/**
	 * The foreign key for the model.
	 *
	 * @var    string
	 */
	protected $foreignKey = 'item_id';

	/**
	 * The fillable fields for the model.
	 *
	 * @var    array
	 */
	protected $fillable = [
		'file_type_id',
		'media_type_id',
		'user_id',
		'slug',
		'title',
		'description_type',
		'description',
		'filename',
		'basename',
		'extension',
		'path',
		'width',
		'height',
		'thumbnail',
		'thumbnail_extension',
		'thumbnail_width',
		'thumbnail_height',
		'date_created',
		'published_at',
	];

	/**
	 * The special typed fields for the model.
	 *
	 * @var    array
	 */
	protected $types = [
		'slug'         => 'unique-slug',
		'published_at' => 'date-time',
	];

	/**
	 * The special formatted fields for the model.
	 *
	 * @var    array
	 */
	protected $formats = [
		'published' => 'trueIfNotNull:published_at',
	];

	/**
	 * The special formatted fields for the model for saving to the database.
	 *
	 * @var    array
	 */
	protected $formatsForDb = [
		'published_at' => 'nullIfBlank',
	];

	/**
	 * The default values for the model.
	 *
	 * @return array
	 */
	public static function defaults()
	{
		$defaults = [
			'description_type' => Fractal::getSetting('Default Content Area Type'),
			'published'        => true,
			'published_at'     => date(Form::getDateTimeFormat()),
		];

		return $defaults;
	}

	/**
	 * Get the validation rules used by the model.
	 *
	 * @param  mixed    $id
	 * @return string
	 */
	public static function validationRules($id = null)
	{
		$rules = [
			'slug'  => ['required'],
			'title' => ['required'],
		];

		return $rules;
	}

	/**
	 * The file type that the media item belongs to.
	 *
	 * @return FileType
	 */
	public function fileType()
	{
		return $this->belongsTo('Regulus\Fractal\Models\FileType');
	}

	/**
	 * The media type that the media item belongs to.
	 *
	 * @return MediaType
	 */
	public function type()
	{
		return $this->belongsTo('Regulus\Fractal\Models\MediaType', 'media_type_id');
	}

	/**
	 * The creator of the media item.
	 *
	 * @return User
	 */
	public function creator()
	{
		return $this->belongsTo(Config::get('auth.model'), 'user_id');
	}

	/**
	 * The sets that the media item belongs to.
	 *
	 * @return Collection
	 */
	public function sets()
	{
		return $this
			->belongsToMany('Regulus\Fractal\Models\MediaSet', 'media_item_sets', 'item_id', 'set_id')
			->withPivot('display_order')
			->orderBy('title');
	}

	/**
	 * The file type of the media item.
	 *
	 * @return string
	 */
	public function getFileType()
	{
		if ($this->fileType)
			return $this->fileType->name;

		return "Unknown: ".$this->extension;
	}

	/**
	 * The file type of the media item.
	 *
	 * @return string
	 */
	public function getType()
	{
		if ($this->type)
			return $this->type->name;

		return "General (".$this->getFileType().")";
	}

	/**
	 * Get the URL for the media item.
	 *
	 * @return string
	 */
	public function getUrl()
	{
		return Fractal::mediaUrl('item/'.$this->slug);
	}

	/**
	 * Get the path of the file.
	 *
	 * @param  boolean  $thumbnail
	 * @return string
	 */
	public function getFilePath($thumbnail = false)
	{
		$path = 'media/';

		if ($this->path != "")
			$path = $path.$this->path.'/';

		if ($thumbnail && $this->thumbnail)
			$path .= "thumbnails/";

		if ($thumbnail && !is_null($this->thumbnail_extension))
			$path .= str_replace($this->extension, $this->thumbnail_extension, $this->filename);
		else
			$path .= $this->filename;

		return $path;
	}

	/**
	 * Get the URL of the file.
	 *
	 * @param  boolean  $thumbnail
	 * @return string
	 */
	public function getFileUrl($thumbnail = false)
	{
		return Site::uploadedFile($this->getFilePath($thumbnail));
	}

	/**
	 * Get the image URL or a placeholder image URL for the file.
	 *
	 * @param  boolean  $thumbnail
	 * @return string
	 */
	public function getImageUrl($thumbnail = false)
	{
		if ($this->getFileType() == "Image" || ($thumbnail && $this->thumbnail))
			return $this->getFileUrl($thumbnail);
		else
			return Site::img('image-not-available', 'regulus/fractal');
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
		if ($this->type != "Image")
			return "&ndash;";

		$dimensions = '<div>'.$this->width.' x '.$this->height.'</div>';
		if ($this->thumbnail)
			$dimensions .= '<div>'.$this->thumbnail_width.' x '.$this->thumbnail_height.'</div>';

		return $dimensions;
	}

	/**
	 * Get the rendered content.
	 *
	 * @return string
	 */
	public function getRenderedDescription()
	{
		return Fractal::renderContent($this->description, $this->description_type);
	}

	/**
	 * Gets the published pages.
	 *
	 * @return Collection
	 */
	public static function getPublished()
	{
		return static::onlyPublished()->orderBy('id')->get();
	}

	/**
	 * Gets only the published pages.
	 *
	 * @return Collection
	 */
	public function scopeOnlyPublished($query)
	{
		return $query->whereNotNull('published_at')->where('published_at', '<=', date('Y-m-d H:i:s'));
	}

	/**
	 * Get the published status.
	 *
	 * @param  mixed    $dateFormat
	 * @return string
	 */
	public function isPublished()
	{
		return !is_null($this->published_at) && strtotime($this->published_at) <= time();
	}

	/**
	 * Check whether article is to be published in the future.
	 *
	 * @param  mixed    $dateFormat
	 * @return string
	 */
	public function isPublishedFuture()
	{
		return !is_null($this->published_at) && strtotime($this->published_at) > time();
	}

	/**
	 * Get the published status string.
	 *
	 * @param  mixed    $dateFormat
	 * @return string
	 */
	public function getPublishedStatus($dateFormat = false)
	{

		$yesNo = [
			'<span class="boolean-true">Yes</span>',
			'<span class="boolean-false">No</span>',
		];

		$status = Format::boolToStr($this->isPublished(), $yesNo);

		if ($this->isPublishedFuture())
			$status .= '<div><small><em>'.Lang::get('fractal::labels.toBePublished', [
				'dateTime' => $this->getPublishedDateTime()
			]).'</em></small></div>';

		return $status;
	}

	/**
	 * Get the last updated date/time.
	 *
	 * @param  mixed    $dateFormat
	 * @return string
	 */
	public function getPublishedDateTime($dateFormat = false)
	{
		if (!$dateFormat)
			$dateFormat = Fractal::getDateTimeFormat();

		return Fractal::dateTimeSet($this->published_at) ? date($dateFormat, strtotime($this->published_at)) : '';
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
			$dateFormat = Fractal::getDateTimeFormat();

		return Fractal::dateTimeSet($this->updated_at) ? date($dateFormat, strtotime($this->updated_at)) : date($dateFormat, strtotime($this->created_at));
	}

	/**
	 * Log the media item view.
	 *
	 * @return void
	 */
	public function logView()
	{
		ContentView::log($this);
	}

	/**
	 * Get the number of media item views.
	 *
	 * @return integer
	 */
	public function getViews()
	{
		return ContentView::getViewsForItem($this);
	}

	/**
	 * Get the number of unique media item views.
	 *
	 * @return integer
	 */
	public function getUniqueViews()
	{
		return ContentView::getUniqueViewsForItem($this);
	}

	/**
	 * Log the media item download.
	 *
	 * @return void
	 */
	public function logDownload()
	{
		ContentDownload::log($this);
	}

	/**
	 * Get the number of media item downloads.
	 *
	 * @return integer
	 */
	public function getDownloads()
	{
		return ContentDownload::getDownloadsForItem($this);
	}

	/**
	 * Get the number of unique media item downloads.
	 *
	 * @return integer
	 */
	public function getUniqueDownloads()
	{
		return ContentDownload::getUniqueDownloadsForItem($this);
	}

	/**
	 * Check whether the media item is hosted externally.
	 *
	 * @return boolean
	 */
	public function isHostedExternally()
	{
		return (bool) $this->hosted_externally;
	}

	/**
	 * Check whether the media item is hosted locally.
	 *
	 * @return boolean
	 */
	public function isHostedLocally()
	{
		return ! $this->isHostedExternally();
	}

	/**
	 * Delete the files for a media item.
	 *
	 * @return integer
	 */
	public function deleteFiles()
	{
		$deleted = 0;

		if ($this->isHostedLocally()) {
			//delete file if it exists
			$filePath = 'uploads/'.$this->getFilePath();
			if (is_file($filePath)) {
				unlink('uploads/'.$this->getFilePath());
				$deleted ++;
			}

			//delete thumbnail image if it exists
			$filePath = 'uploads/'.$this->getFilePath(true);
			if (is_file($filePath)) {
				unlink('uploads/'.$this->getFilePath(true));
				$deleted ++;
			}
		}

		return $deleted;
	}

	/**
	 * Get media items for a specific extension.
	 *
	 * @param  string   $extension
	 * @return MediaItem
	 */
	public static function getForExtension($extension)
	{
		return static::where('extension', $extension)->orderBy('name')->get();
	}

	/**
	 * Get media item search results.
	 *
	 * @param  array    $searchData
	 * @return Collection
	 */
	public static function getSearchResults($searchData)
	{
		$media = static::orderBy($searchData['sortField'], $searchData['sortOrder']);

		if ($searchData['sortField'] != "id")
			$media->orderBy('id', 'asc');

		if ($searchData['terms'] != "")
			$media
				->orWhere('title', 'like', $searchData['likeTerms'])
				->orWhere('description', 'like', $searchData['likeTerms']);

		$filters = $searchData['filters'];
		if (!empty($filters)) {
			$allowedFilters = [
				'media_type_id',
				'media_set_id',
			];

			foreach ($allowedFilters as $allowedFilter) {
				if (isset($filters[$allowedFilter]) && $filters[$allowedFilter] && $filters[$allowedFilter] != "")
					$media->where($allowedFilter, $filters['media_type_id']);
			}
			
		}

		return $media->paginate($searchData['itemsPerPage']);
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
		$originalExtension = strtolower(File::extension($originalFilename));

		//make sure filename is unique and then again remove extension to set basename
		$uniqueFilename = Format::unique(Format::slug(Input::get('title')).'.'.$originalExtension, 'media_items', 'filename', $id, true);
		$basename       = str_replace('.'.$originalExtension, '', $uniqueFilename);

		$path = "uploads/media";

		$fileType = FileType::find(Input::get('file_type_id'));
		if (!empty($fileType))
			$path .= '/'.$fileType->slug;

		$config = [
			'path'            => $path,
			'fields'          => ['file', 'thumbnail_image'],
			'fieldThumb'      => 'thumbnail_image',
			'filename'        => $basename,
			'createDirectory' => true,
			'overwrite'       => true,
			'maxFileSize'     => '8MB',
			'imageThumb'      => true,
		];

		//set image resize settings
		if (!empty($fileType)) {
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

			$config['imageThumb']      = true; //always create thumbnail for media items
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