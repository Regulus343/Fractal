<?php namespace Regulus\Fractal\Models;

use Aquanode\Formation\BaseModel;

use Fractal;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;

use \Form as Form;
use \Format as Format;
use \Site as Site;

class MediaSet extends BaseModel {

	/**
	 * The database table used by the model.
	 *
	 * @var    string
	 */
	protected $table = 'media_sets';

	/**
	 * The foreign key for the model.
	 *
	 * @var    string
	 */
	protected $foreignKey = 'set_id';

	/**
	 * The fillable fields for the model.
	 *
	 * @var    array
	 */
	protected $fillable = array(
		'user_id',
		'slug',
		'title',
		'description_type',
		'description',
		'published_at',
	);

	/**
	 * The special typed fields for the model.
	 *
	 * @var    array
	 */
	protected $types = array(
		'slug'         => 'unique-slug',
		'published_at' => 'date-time',
	);

	/**
	 * The special formatted fields for the model.
	 *
	 * @var    array
	 */
	protected $formats = array(
		'published' => 'trueIfNotNull:published_at',
	);

	/**
	 * The special formatted fields for the model for saving to the database.
	 *
	 * @var    array
	 */
	protected $formatsForDb = array(
		'published_at' => 'nullIfBlank',
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
			'slug'  => array('required'),
			'title' => array('required'),
		);
	}

	/**
	 * The items that the media set belongs to.
	 *
	 * @return Collection
	 */
	public function items()
	{
		return $this
			->belongsToMany('Regulus\Fractal\Models\MediaItem', 'media_item_sets', 'set_id', 'item_id')
			->withPivot('display_order')
			->orderBy('display_order');
	}

	/**
	 * Get the URL for the media type.
	 *
	 * @return string
	 */
	public function getUrl()
	{
		return Fractal::mediaUrl('set/'.$this->slug);
	}

}