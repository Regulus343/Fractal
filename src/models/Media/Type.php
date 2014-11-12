<?php namespace Regulus\Fractal\Models\Media;

use Regulus\Formation\BaseModel;

use Fractal;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;

use \Form;
use \Format;
use \Site;

class Type extends BaseModel {

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
		'type_id',
		'parent_id',
		'slug',
		'name',
		'extensions',
	];

	/**
	 * The special typed fields for the model.
	 *
	 * @var    array
	 */
	protected $types = [
		'slug' => 'unique-slug',
	];

	/**
	 * Get the validation rules used by the model.
	 *
	 * @param  mixed    $id
	 * @return string
	 */
	public static function validationRules($id = null)
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
	 * The number of items that belong to the media type.
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
		return Fractal::mediaUrl('type/'.$this->slug);
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