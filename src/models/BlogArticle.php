<?php namespace Regulus\Fractal\Models;

use Aquanode\Formation\BaseModel;

use Fractal;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;

use \Form as Form;
use \Format as Format;

use Regulus\Fractal\Traits\PublishingTrait;

class BlogArticle extends BaseModel {

	/**
	 * The database table used by the model.
	 *
	 * @var    string
	 */
	protected $table = 'blog_articles';

	/**
	 * The foreign key for the model.
	 *
	 * @var    string
	 */
	protected $foreignKey = 'article_id';

	/**
	 * The fillable fields for the model.
	 *
	 * @var    array
	 */
	protected $fillable = array(
		'blog_id',
		'slug',
		'title',
		'layout_template_id',
		'layout',
		'user_id',
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
	 * The default values for the model.
	 *
	 * @return array
	 */
	public static function defaults()
	{
		$defaults = array(
			'layout_template_id' => 1,
			'published'          => true,
			'published_at'       => date(Form::getDateTimeFormat()),
		);

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
		$rules = array(
			'title' => array('required'),
			'slug'  => array('required'),
		);

		if (Form::post()) {
			foreach (Form::getValuesObject('content_areas') as $number => $values)
			{
				if (Form::getValueFromObject('title', $values) != "" || Form::getValueFromObject('pivot.layout_tag', $values) != ""
				|| Form::getValueFromObject('content_html', $values) != "" || Form::getValueFromObject('content_markdown', $values) != "")
				{
					$contentField = Form::getValueFromObject('content_type', $values) == "HTML" ? "content_html" : "content_markdown";

					$rules = array_merge($rules, array(
						'content_areas.'.$number.'.pivot.layout_tag' => array('required'),
						'content_areas.'.$number.'.content_type'     => array('required'),
						'content_areas.'.$number.'.'.$contentField   => array('required'),
					));
				}
			}
		}

		return $rules;
	}

	/**
	 * The blog that the article belong to.
	 *
	 * @return Menu
	 */
	public function blog()
	{
		return $this->belongsTo('Regulus\Fractal\Models\Blog');
	}

	/**
	 * The author of the article.
	 *
	 * @return User
	 */
	public function author()
	{
		return $this->belongsTo(Config::get('auth.model'), 'user_id');
	}

	/**
	 * The layout template that the article belongs to.
	 *
	 * @return Collection
	 */
	public function layoutTemplate()
	{
		return $this->belongsTo('Regulus\Fractal\Models\ContentLayoutTemplate', 'layout_template_id');
	}

	/**
	 * Get the URL for the article.
	 *
	 * @return string
	 */
	public function getUrl()
	{
		return Fractal::blogUrl('article/'.$this->slug);
	}

	/**
	 * Get the layout for the article.
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
	 * Get the layout tags for the article's layout.
	 *
	 * @return array
	 */
	public function getLayoutTags()
	{
		$layout = $this->getLayout();

		return Fractal::getLayoutTagsFromLayout($layout);
	}

	/**
	 * Get the rendered content for the article.
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
	 * The content areas that the article belongs to.
	 *
	 * @return Collection
	 */
	public function contentAreas()
	{
		return $this
			->belongsToMany('Regulus\Fractal\Models\BlogContentArea', 'blog_article_content_areas', 'article_id', 'area_id')
			->withPivot('layout_tag')
			->orderBy('title');
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

		$yesNo = array(
			'<span class="boolean-true">Yes</span>',
			'<span class="boolean-false">No</span>',
		);

		$status = Format::boolToStr($this->isPublished(), $yesNo);

		if ($this->isPublishedFuture())
			$status .= '<div><small><em>'.Lang::get('fractal::labels.toBePublished', array(
				'dateTime' => $this->getPublishedDateTime()
			)).'</em></small></div>';

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
	 * Log the article view.
	 *
	 * @return void
	 */
	public function logView()
	{
		ContentView::log($this);
	}

	/**
	 * Get the number of article views.
	 *
	 * @return Collection
	 */
	public function getViews()
	{
		return ContentView::getViewsForItem($this);
	}

	/**
	 * Get the number of unique article views.
	 *
	 * @return Collection
	 */
	public function getUniqueViews()
	{
		return ContentView::getUniqueViewsForItem($this);
	}

}