<?php namespace Regulus\Fractal\Models;

use Regulus\Formation\BaseModel;

use Fractal;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;

use \Form;
use \Format;

use Regulus\Fractal\Traits\PublishingTrait;

class ContentPage extends BaseModel {

	/**
	 * The database table used by the model.
	 *
	 * @var    string
	 */
	protected $table = 'content_pages';

	/**
	 * The foreign key for the model.
	 *
	 * @var    string
	 */
	protected $foreignKey = 'page_id';

	/**
	 * The fillable fields for the model.
	 *
	 * @var    array
	 */
	protected $fillable = [
		'slug',
		'title',
		'layout_template_id',
		'layout',
		'user_id',
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
			'layout_template_id' => 1,
			'published'          => true,
			'published_at'       => date(Form::getDateTimeFormat()),
		];

		$defaults = array_merge($defaults, static::addPrefixToDefaults(ContentArea::defaults(), 'content_areas.1'));

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

		if (Form::post()) {
			foreach (Form::getValuesObject('content_areas') as $number => $values)
			{
				if (Form::getValueFromObject('title', $values) != "" || Form::getValueFromObject('pivot.layout_tag', $values) != ""
				|| Form::getValueFromObject('content_html', $values) != "" || Form::getValueFromObject('content_markdown', $values) != "")
				{
					$contentField = Form::getValueFromObject('content_type', $values) == "HTML" ? "content_html" : "content_markdown";

					$rules = array_merge($rules, [
						'content_areas.'.$number.'.pivot.layout_tag' => ['required'],
						'content_areas.'.$number.'.content_type'     => ['required'],
						'content_areas.'.$number.'.'.$contentField   => ['required'],
					]);
				}
			}
		}

		return $rules;
	}

	/**
	 * The creator of the page.
	 *
	 * @return User
	 */
	public function creator()
	{
		return $this->belongsTo(Config::get('auth.model'), 'user_id');
	}

	/**
	 * The layout template that the page belongs to.
	 *
	 * @return Collection
	 */
	public function layoutTemplate()
	{
		return $this->belongsTo('Regulus\Fractal\Models\ContentLayoutTemplate', 'layout_template_id');
	}

	/**
	 * Get the URL for the page.
	 *
	 * @return string
	 */
	public function getUrl()
	{
		return URL::to(Config::get('fractal::pageUri').'/'.$this->slug);
	}

	/**
	 * Get the layout for the page.
	 *
	 * @param  array    $usePostData
	 * @return string
	 */
	public function getLayout($usePostData = false)
	{
		if ($usePostData && Input::old('layout')) {
			if (Input::old('layout_template_id'))
				return Input::old('layout_template_id');

			return Input::old('layout');
		} else {
			if ($this->layoutTemplate)
				return $this->layoutTemplate->layout;

			return $this->layout;
		}
	}

	/**
	 * Get the layout tags for the page's layout.
	 *
	 * @return array
	 */
	public function getLayoutTags()
	{
		$layout = $this->getLayout();

		return Fractal::getLayoutTagsFromLayout($layout);
	}

	/**
	 * Get the rendered content for the page.
	 *
	 * @return string
	 */
	public function getRenderedContent()
	{
		$content = $this->getLayout();

		foreach ($this->contentAreas as $contentArea) {
			$content = $contentArea->renderContentToLayout($content);
		}

		return $content;
	}

	/**
	 * The content areas that the page belongs to.
	 *
	 * @return Collection
	 */
	public function contentAreas()
	{
		return $this
			->belongsToMany('Regulus\Fractal\Models\ContentArea', 'content_page_areas', 'page_id', 'area_id')
			->withPivot('layout_tag')
			->orderBy('title');
	}

	/**
	 * The menu item that the page belongs to.
	 *
	 * @return Collection
	 */
	public function menuItems()
	{
		return $this->hasMany('Regulus\Fractal\Models\MenuItem');
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
	 * Check whether page is to be published in the future.
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
	 * Log the page view.
	 *
	 * @return void
	 */
	public function logView()
	{
		ContentView::log($this);
	}

	/**
	 * Get the number of page views.
	 *
	 * @return integer
	 */
	public function getViews()
	{
		return ContentView::getViewsForItem($this);
	}

	/**
	 * Get the number of unique page views.
	 *
	 * @return integer
	 */
	public function getUniqueViews()
	{
		return ContentView::getUniqueViewsForItem($this);
	}

	/**
	 * Get page search results.
	 *
	 * @param  array    $searchData
	 * @return Collection
	 */
	public static function getSearchResults($searchData)
	{
		$pages = static::orderBy($searchData['sortField'], $searchData['sortOrder']);

		if ($searchData['sortField'] != "id")
			$pages->orderBy('id', 'asc');

		if ($searchData['terms'] != "")
			$pages->where(function($query) use ($searchData) {
				$query
					->where('title', 'like', $searchData['likeTerms'])
					->orWhere('slug', 'like', $searchData['likeTerms']);
			});

		return $pages->paginate($searchData['itemsPerPage']);
	}

}