<?php namespace Regulus\Fractal\Models\Media;

use Regulus\Formation\Models\Base;

use Fractal;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;

use Regulus\Formation\Facade as Form;
use Regulus\TetraText\Facade as Format;

use Regulus\Fractal\Traits\Publishable;

class Set extends Base {

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
	protected $fillable = [
		'user_id',
		'slug',
		'title',
		'description_type',
		'description',
		'image_gallery',
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
	 * @param  string   $type
	 * @return array
	 */
	public static function validationRules($id = null, $type = 'default')
	{
		return [
			'slug'  => ['required'],
			'title' => ['required'],
			'items' => ['required'],
		];
	}

	/**
	 * The items that the media set belongs to.
	 *
	 * @return Collection
	 */
	public function items()
	{
		return $this
			->belongsToMany('Regulus\Fractal\Models\Media\Item', 'media_item_sets', 'set_id', 'item_id')
			->withPivot('display_order')
			->withTimestamps()
			->orderBy('display_order');
	}

	/**
	 * The number of items that belong to the media set.
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
		return Fractal::mediaUrl('s/'.$this->slug);
	}

	/**
	 * Save the items and their display orders for the media set.
	 *
	 * @return string
	 */
	public function saveItems($itemIds)
	{
		$items = [];
		foreach ($itemIds as $i => $itemId) {
			$items[$itemId] = ['display_order' => ($i + 1)];
		}

		$this->items()->sync($items);
	}

	/**
	 * Get the rendered description.
	 *
	 * @param  boolean  $previewOnly
	 * @return string
	 */
	public function getRenderedDescription($previewOnly = false)
	{
		$config = [
			'contentType' => $this->description_type,
			'previewOnly' => $previewOnly,
		];

		return Fractal::renderContent($this->description, $config);
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
	 * Check whether collection of media sets have any published items.
	 *
	 * @param  Collection $mediaSets
	 * @return boolean
	 */
	public static function publishedItemInSets($mediaSets)
	{
		foreach ($mediaSets as $mediaSet)
		{
			if ($mediaSet->items()->onlyPublished()->count())
				return true;
		}

		return false;
	}

	/**
	 * Get media set search results.
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

		Fractal::setRequestedPage();

		return $categories->paginate($searchData['itemsPerPage']);
	}

}