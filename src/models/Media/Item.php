<?php namespace Regulus\Fractal\Models\Media;

use Regulus\Formation\Models\Base;

use Fractal;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

use Auth;
use Regulus\Formation\Facade as Form;
use Regulus\TetraText\Facade as Format;
use Regulus\SolidSite\Facade as Site;
use Regulus\Upstream\Facade as Upstream;

use Regulus\Fractal\Models\Content\FileType;
use Regulus\Fractal\Models\Content\View as ContentView;
use Regulus\Fractal\Models\Content\Download as ContentDownload;

use Regulus\Fractal\Traits\Publishable;

use AlfredoRamos\ParsedownExtra\Facades\ParsedownExtra as Markdown;

class Item extends Base {

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
		'hosted_externally',
		'hosted_content_type',
		'hosted_content_uri',
		'hosted_content_thumbnail_url',
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
		'comments_enabled',
		'date_created',
		'published_at',
	];

	/**
	 * The special typed fields for the model.
	 *
	 * @var    array
	 */
	protected $types = [
		'slug'             => 'unique-slug',
		'comments_enabled' => 'checkbox',
		'date_created'     => 'date',
		'published_at'     => 'date-time',
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
		'hosted_content_thumbnail_url' => 'nullIfBlank',
		'date_created'                 => 'nullIfBlank',
		'published_at'                 => 'nullIfBlank',
	];

	/**
	 * The restricted slugs for the model.
	 *
	 * @var    array
	 */
	protected static $restrictedSlugs = [
		'admin',
		'i',
		'item',
		's',
		'set',
		't',
		'type',
		'p',
		'page',
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
			'comments_enabled' => Fractal::getSetting('Enable Media Item Comments', true),
			'published'        => true,
			'published_at'     => date(Form::getDateTimeFormat()),
		];

		return $defaults;
	}

	/**
	 * Get the validation rules used by the model.
	 *
	 * @param  mixed    $id
	 * @param  string   $type
	 * @return array
	 */
	public static function validationRules($id = null, $type = 'default')
	{
		$rules = [
			'slug'  => ['required', 'lowercase_not_in:'.implode(',', static::$restrictedSlugs)],
			'title' => ['required'],
		];

		if (Input::get('hosted_externally')) {
			$rules['hosted_content_type'] = ['required'];
			$rules['hosted_content_uri']  = ['required'];
		}

		return $rules;
	}

	/**
	 * The file type that the media item belongs to.
	 *
	 * @return FileType
	 */
	public function fileType()
	{
		return $this->belongsTo('Regulus\Fractal\Models\Content\FileType');
	}

	/**
	 * The media type that the media item belongs to.
	 *
	 * @return MediaType
	 */
	public function type()
	{
		return $this->belongsTo('Regulus\Fractal\Models\Media\Type', 'media_type_id');
	}

	/**
	 * The creator of the media item.
	 *
	 * @return User
	 */
	public function creator()
	{
		return $this->belongsTo(config('auth.model'), 'user_id');
	}

	/**
	 * The sets that the media item belongs to.
	 *
	 * @return Collection
	 */
	public function sets()
	{
		return $this
			->belongsToMany('Regulus\Fractal\Models\Media\Set', 'media_item_sets', 'item_id', 'set_id')
			->withPivot('display_order')
			->withTimestamps()
			->orderBy('title');
	}

	/**
	 * The number of media sets that the item belongs to.
	 *
	 * @return integer
	 */
	public function getNumberOfSets()
	{
		return $this->sets()->count();
	}

	/**
	 * Get the IDs of the media sets.
	 *
	 * @return array
	 */
	public function getSetIds()
	{
		$ids = [];

		foreach ($this->sets as $set) {
			$ids[] = (int) $set->id;
		}

		return $ids;
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
	 * Get the Markdown-formatted title of the media item.
	 *
	 * @return string
	 */
	public function getTitle()
	{
		return Markdown::line($this->title);
	}

	/**
	 * Get the URL for the media item.
	 *
	 * @return string
	 */
	public function getUrl()
	{
		return Fractal::mediaUrl((!config('media.short_routes') ? 'i/' : '').$this->slug);
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
			$path .= $this->path.'/';

		if ($thumbnail && $this->thumbnail)
			$path .= "thumbnails/";

		if ($thumbnail && !is_null($this->thumbnail_extension))
		{
			$filename = !is_null($this->filename) ? $this->filename : $this->slug.'.'.$this->thumbnail_extension;

			$path .= str_replace($this->extension, $this->thumbnail_extension, $filename);
		} else {
			$path .= $this->filename;
		}

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
	 * Check if a media item has a file.
	 *
	 * @return boolean
	 */
	public function hasFile()
	{
		return !is_null($this->filename);
	}

	/**
	 * Check if a media item has a thumbnail image.
	 *
	 * @return boolean
	 */
	public function hasThumbnailImage()
	{
		return $this->thumbnail;
	}

	/**
	 * Get the image URL or a placeholder image URL for the media item.
	 *
	 * @param  boolean  $thumbnail
	 * @return string
	 */
	public function getImageUrl($thumbnail = false)
	{
		if ($this->hostedExternally('YouTube') && (!$this->thumbnail || !$thumbnail))
			return 'http://img.youtube.com/vi/'.$this->hosted_content_uri.'/0.jpg';

		elseif ($this->hostedExternally('Vimeo') && !$this->thumbnail && !is_null($this->hosted_content_thumbnail_url))
			return $this->hosted_content_thumbnail_url;

		elseif ($this->getFileType() == "Image" || ($thumbnail && $this->thumbnail))
			return $this->getFileUrl($thumbnail);

		return Fractal::getImageUrlFromConfig('cms.placeholder_image');
	}

	/**
	 * Get the thumbnail image URL or a placeholder image URL for the media item.
	 *
	 * @return string
	 */
	public function getThumbnailImageUrl()
	{
		return $this->getImageUrl(true);
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
	 * Check whether media item is hosted externally.
	 *
	 * @param  mixed    $type
	 * @return boolean
	 */
	public function hostedExternally($type = null)
	{
		if (is_null($type))
			return (boolean) $this->hosted_externally;
		else
			return (boolean) ($this->hosted_externally && $this->hosted_content_type == $type);
	}

	/**
	 * Check if a media item requires a media source.
	 *
	 * @return boolean
	 */
	public function mediaSourceRequired()
	{
		if ($this->hosted_externally)
			return false;

		if ($this->type && !$this->type->media_source_required)
			return false;

		return true;
	}

	/**
	 * Get the markup for the embedded content of the media item.
	 *
	 * @param  mixed    $type
	 * @return string
	 */
	public function getEmbeddedContent($type = null)
	{
		if (!$this->hostedExternally($type))
			return "";

		if (is_null($type))
			$type = $this->hosted_content_type;

		return Fractal::getEmbeddedContent($type, $this->hosted_content_uri);
	}

	/**
	 * Get the markup for the media item.
	 *
	 * @param  boolean  $contentInserted
	 * @return string
	 */
	public function getContent($contentInserted = false)
	{
		return View::make(Fractal::view('media.view.partials.item_content', true), [
			'mediaItem'       => $this,
			'contentInserted' => $contentInserted,
		])->render();
	}

	/**
	 * Get the rendered description.
	 *
	 * @param  array    $config
	 * @return string
	 */
	public function getRenderedDescription($config = [])
	{
		$previewOnly = isset($config['previewOnly']) ? $config['previewOnly'] : false;
		$sanitized   = isset($config['sanitized'])   ? $config['sanitized']   : false;

		$config['contentType'] = $this->description_type;

		$description = Fractal::renderContent($this->description, $config);

		if ($sanitized && $description != "")
		{
			$description = '<div class="item-description">'.$description.'</div>';
			$description = str_replace('<', '&lt;', $description);
			$description = str_replace('>', '&gt;', $description);
			$description = str_replace('"', "'", $description);
		}

		return $description;
	}

	/**
	 * Check whether comments are enabled for item.
	 *
	 * @return boolean
	 */
	public function commentsEnabled()
	{
		return Fractal::getSetting('Enable Media Item Comments', true) && $this->comments_enabled;
	}

	/**
	 * Gets the published items.
	 *
	 * @return Collection
	 */
	public static function getPublished()
	{
		return static::onlyPublished()->orderBy('id')->get();
	}

	/**
	 * Gets only the published items.
	 *
	 * @param  boolean  $allowAdmin
	 * @return Collection
	 */
	public function scopeOnlyPublished($query, $allowAdmin = false)
	{
		if ($allowAdmin && Auth::is('admin'))
			return $query;

		return $query->whereNotNull('media_items.published_at')->where('media_items.published_at', '<=', date('Y-m-d H:i:s'));
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
			$status .= '<div><small><em>'.Fractal::trans('labels.toBePublished', [
				'dateTime' => $this->getPublishedDateTime()
			]).'</em></small></div>';

		return $status;
	}

	/**
	 * Get the published date/time.
	 *
	 * @param  mixed    $dateFormat
	 * @return string
	 */
	public function getPublishedDateTime($dateFormat = false)
	{
		if (!$dateFormat)
			$dateFormat = Fractal::getDateTimeFormat();

		return Fractal::dateTimeSet($this->published_at) ? date($dateFormat, strtotime($this->published_at)) : null;
	}

	/**
	 * Get the published date.
	 *
	 * @param  mixed    $dateFormat
	 * @return string
	 */
	public function getPublishedDate($dateFormat = false)
	{
		if (!$dateFormat)
			$dateFormat = Fractal::getDateFormat();

		return Fractal::dateSet($this->published_at) ? date($dateFormat, strtotime($this->published_at)) : null;
	}

	/**
	 * Get the date the item was created.
	 *
	 * @param  mixed    $dateFormat
	 * @return string
	 */
	public function getCreatedDate($dateFormat = false)
	{
		if (!$dateFormat)
			$dateFormat = 'F Y';

		return Fractal::dateSet($this->date_created) ? date($dateFormat, strtotime($this->date_created)) : null;
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
		$media = static::select(['media_items.*'])
			->leftJoin('media_item_sets', 'media_items.id', '=', 'media_item_sets.item_id')
			->groupBy('media_items.id')
			->orderBy($searchData['sortField'], $searchData['sortOrder']);

		if ($searchData['sortField'] != "id")
			$media->orderBy('id', 'asc');

		if ($searchData['terms'] != "")
			$media
				->orWhere('title', 'like', $searchData['likeTerms'])
				->orWhere('description', 'like', $searchData['likeTerms']);

		$filters = $searchData['filters'];
		if (!empty($filters))
		{
			$allowedFilters = [
				'media_type_id' => 'media_items.media_type_id',
				'media_set_id'  => 'media_item_sets.set_id',
			];

			foreach ($allowedFilters as $allowedFilter => $filterField)
			{
				if (isset($filters[$allowedFilter]) && $filters[$allowedFilter] && $filters[$allowedFilter] != "")
					$media->where($filterField, $filters[$allowedFilter]);
			}
		}

		Fractal::setRequestedPage();

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
		else
			$path .= '/other';

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