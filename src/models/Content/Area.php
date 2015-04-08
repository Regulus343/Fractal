<?php namespace Regulus\Fractal\Models\Content;

use Regulus\Formation\Models\Base;

use Fractal;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;

class Area extends Base {

	/**
	 * The database table used by the model.
	 *
	 * @var    string
	 */
	protected $table = 'content_areas';

	/**
	 * The foreign key for the model.
	 *
	 * @var    string
	 */
	protected $foreignKey = 'area_id';

	/**
	 * The fillable fields for the model.
	 *
	 * @var    array
	 */
	protected $fillable = [
		'title',
		'content_type',
		'content',
	];

	/**
	 * The special typed fields for the model.
	 *
	 * @var    string
	 */
	protected $types = [
		'updated_at' => 'date-time',
	];

	/**
	 * The default values for the model.
	 *
	 * @return array
	 */
	public static function defaults()
	{
		$defaults = [
			'content_type' => 'Markdown',
		];

		return static::addPrefixToDefaults($defaults);
	}

	/**
	 * The creator of the content area.
	 *
	 * @return User
	 */
	public function creator()
	{
		return $this->belongsTo(Config::get('auth.model'), 'user_id');
	}

	/**
	 * The content pages that the area belongs to.
	 *
	 * @return Collection
	 */
	public function pages()
	{
		return $this->belongsToMany('Regulus\Fractal\Models\Content\Page', 'content_page_areas', 'area_id', 'page_id');
	}

	/**
	 * Get the IDs of the content pages.
	 *
	 * @return array
	 */
	public function getPageIds()
	{
		$ids = [];

		foreach ($this->pages as $page) {
			$ids[] = (int) $page->id;
		}

		return $ids;
	}

	/**
	 * Get the title of the content area.
	 *
	 * @return string
	 */
	public function getTitle()
	{
		if ($this->title != "")
			return $this->title;

		if ($this->pages()->count()) {
			$title = $this->pages[0]->title;

			$pivotItem = DB::table('content_page_areas')
				->select('layout_tag')
				->where('area_id', $this->id)
				->where('page_id', $this->pages[0]->id)
				->first();

			if (!empty($pivotItem))
				$title .= ' - '.ucwords(str_replace('-', ' ', $pivotItem->layout_tag));

			return $title;
		}

		return Fractal::trans('labels.untitled');
	}

	/**
	 * Get the rendered content.
	 *
	 * @return string
	 */
	public function getRenderedContent()
	{
		return Fractal::renderContent($this->content, ['contentType' => $this->content_type]);
	}

	/**
	 * Apply rendered content to layout.
	 *
	 * @return string
	 */
	public function renderContentToLayout($content)
	{
		return str_replace('{{'.$this->pivot->layout_tag.'}}', $this->getRenderedContent(), $content);
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

}