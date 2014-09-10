<?php namespace Regulus\Fractal\Models;

use Aquanode\Formation\BaseModel;

use Fractal;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;

use \Form as Form;
use \Format as Format;
use \Site as Site;

class MediaType extends BaseModel {

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
	protected $fillable = array(
		'type_id',
		'parent_id',
		'slug',
		'name',
		'extensions',
	);

	/**
	 * The special typed fields for the model.
	 *
	 * @var    array
	 */
	protected $types = array(
		'slug' => 'unique-slug',
	);

	/**
	 * Get the validation rules used by the model.
	 *
	 * @param  mixed    $id
	 * @return string
	 */
	public static function validationRules($id = null)
	{
		return array(
			'slug' => array('required'),
			'name' => array('required'),
		);
	}

	/**
	 * The file type that the media type belongs to.
	 *
	 * @return FileType
	 */
	public function fileType()
	{
		return $this->belongsTo('Regulus\Fractal\Models\FileType', 'file_type_id');
	}

	/**
	 * The media type that the media type belongs to.
	 *
	 * @return MediaType
	 */
	public function parentMediaType()
	{
		return $this->belongsTo('Regulus\Fractal\Models\MediaType', 'parent_id');
	}

	/**
	 * The media type that belongs to the media type.
	 *
	 * @return MediaType
	 */
	public function childMediaType()
	{
		return $this->hasOne('Regulus\Fractal\Models\MediaType', 'parent_id');
	}

	/**
	 * The items that belong to the media type.
	 *
	 * @return Collection
	 */
	public function items()
	{
		return $this->hasMany('Regulus\Fractal\Models\MediaItem');
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

}