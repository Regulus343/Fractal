<?php namespace Regulus\Fractal\Models\Media;

use Regulus\Formation\Models\Base;

use Fractal;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;

use Form;
use Format;

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
		'description_rendered',
		'description_rendered_preview',
		'image_gallery',
		'published',
		'published_at',
	];

	/**
	 * The special typed fields for the model.
	 *
	 * @var    array
	 */
	protected $types = [
		'slug'         => 'unique-slug',
		'published'    => 'checkbox',
		'published_at' => 'date-time',
	];

	/**
	 * The special formatted fields for the model.
	 *
	 * @var    array
	 */
	protected $formats = [];

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
	 * @param  array    $config
	 * @return string
	 */
	public function getRenderedDescription($config = [])
	{
		$configDefault = [
			'contentUrl'            => $this->getUrl(),
			'contentType'           => $this->description_type,
			'previewOnly'           => false,
			'render'                => false,
			'viewButton'            => true,
			'viewButtonPlaceholder' => true,
		];

		$config = array_merge($configDefault, $config);

		if (!$config['render'])
		{
			$description = null;

			if ($config['previewOnly'])
			{
				if (!is_null($this->description_rendered_preview))
					return Fractal::addViewButtonToContent($this->description_rendered_preview, $config);
			}
			else
			{
				if (!is_null($this->description_rendered))
					return Fractal::renderContentViews($this->description_rendered);
			}
		}

		$description = Fractal::renderContent($this->description, $config);

		if ($config['previewOnly'])
			$this->description_rendered_preview = Fractal::addViewButtonToContent($description, $config);
		else
			$this->description_rendered = $description;

		$this->save();

		$description = Fractal::addViewButtonToContent($description, $config);

		return $description;
	}

	/**
	 * Render the description.
	 *
	 * @param  array    $config
	 * @return string
	 */
	public function renderDescription($config = [])
	{
		$config['render'] = true;

		return $this->getRenderedDescription($config);
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
		return $query
			->where('published', true)
			->whereNotNull('published_at')
			->where('published_at', '<=', date('Y-m-d H:i:s'));
	}

	/**
	 * Get the published status.
	 *
	 * @return boolean
	 */
	public function isPublished()
	{
		return $this->published && !is_null($this->published_at) && strtotime($this->published_at) <= time();
	}

	/**
	 * Check whether article is to be published in the future.
	 *
	 * @return boolean
	 */
	public function isPublishedFuture()
	{
		return $this->published && !is_null($this->published_at) && strtotime($this->published_at) > time();
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
			'<span class="boolean-true" title="'.$this->getPublishedDateTime($dateFormat).'">'.Fractal::trans('labels.yes').'</span>',
			'<span class="boolean-false">'.Fractal::trans('labels.no').'</span>',
		];

		$status = Format::boolToStr($this->isPublished(), $yesNo);

		if ($this->isPublishedFuture())
			$status .= '<div><small><em>'.Fractal::trans('labels.toBePublished', [
				'dateTime' => $this->getPublishedDateTime($dateFormat)
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