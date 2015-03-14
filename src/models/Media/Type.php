<?php namespace Regulus\Fractal\Models\Media;

use Regulus\Formation\Models\Base;

use Fractal;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;

use \Form;
use \Format;
use \Site;

class Type extends Base {

	/**
	 * The database table used by the model.
	 *
	 * @var    string
	 */
	protected $table = 'media_types';

	/**
	 * The foreign key for the model.
	 *
	 * @var    string
	 */
	protected $foreignKey = 'media_type_id';

	/**
	 * The fillable fields for the model.
	 *
	 * @var    array
	 */
	protected $fillable = [
		'file_type_id',
		'parent_id',
		'slug',
		'name',
		'name_plural',
		'extensions',
		'media_source_required',
	];

	/**
	 * The special typed fields for the model.
	 *
	 * @var    array
	 */
	protected $types = [
		'slug'                  => 'unique-slug',
		'media_source_required' => 'checkbox',
	];

	/**
	 * The special formatted fields for the model for saving to the database.
	 *
	 * @var    array
	 */
	protected $formatsForDb = [
		'file_type_id' => 'nullIfBlank',
		'name_plural'  => 'nullIfBlank',
	];

	/**
	 * The default values for the model.
	 *
	 * @return array
	 */
	public static function defaults()
	{
		$defaults = [
			'media_source_required' => true,
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
		return [
			'slug' => ['required'],
			'name' => ['required'],
		];
	}

	/**
	 * The file type that the media type belongs to.
	 *
	 * @return FileType
	 */
	public function fileType()
	{
		return $this->belongsTo('Regulus\Fractal\Models\Content\FileType', 'file_type_id');
	}

	/**
	 * The media type that the media type belongs to.
	 *
	 * @return MediaType
	 */
	public function parentMediaType()
	{
		return $this->belongsTo('Regulus\Fractal\Models\Media\Type', 'parent_id');
	}

	/**
	 * The media type that belongs to the media type.
	 *
	 * @return MediaType
	 */
	public function childMediaType()
	{
		return $this->hasOne('Regulus\Fractal\Models\Media\Type', 'parent_id');
	}

	/**
	 * The items that belong to the media type.
	 *
	 * @return Collection
	 */
	public function items()
	{
		return $this->hasMany('Regulus\Fractal\Models\Media\Item');
	}

	/**
	 * Get the name of a media type.
	 *
	 * @param  boolean  $plural
	 * @return string
	 */
	public function getName($plural = false)
	{
		if ($plural)
			return !is_null($this->name_plural) ? $this->name_plural : Str::plural($this->name);
		else
			return $this->name;
	}

	/**
	 * Get the number of items that belong to the media type.
	 *
	 * @return integer
	 */
	public function getNumberOfItems()
	{
		return $this->items()->count();
	}

	/**
	 * Get the URL for the media type.
	 *
	 * @return string
	 */
	public function getUrl()
	{
		return Fractal::mediaUrl('t/'.$this->slug);
	}

	/**
	 * Get the file type for the media type.
	 *
	 * @return string
	 */
	public function getFileType()
	{
		if ($this->fileType)
			return $this->fileType->name;

		return null;
	}

	/**
	 * Check whether collection of media types have any published items.
	 *
	 * @param  Collection $mediaTypes
	 * @return boolean
	 */
	public static function publishedItemInTypes($mediaTypes)
	{
		foreach ($mediaTypes as $mediaType)
		{
			if ($mediaType->items()->onlyPublished()->count())
				return true;
		}

		return false;
	}

	/**
	 * Get media type search results.
	 *
	 * @param  array    $searchData
	 * @return Collection
	 */
	public static function getSearchResults($searchData)
	{
		$categories = static::orderBy($searchData['sortField'], $searchData['sortOrder']);

		if ($searchData['sortField'] != "id")
			$categories->orderBy('id', 'asc');

		if ($searchData['terms'] != "")
			$categories->where('name', 'like', $searchData['likeTerms']);

		return $categories->paginate($searchData['itemsPerPage']);
	}

}