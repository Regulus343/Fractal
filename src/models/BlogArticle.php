<?php namespace Regulus\Fractal\Models;

use Aquanode\Formation\BaseModel;

use Fractal;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

use \Form as Form;

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
		'slug',
		'title',
		'layout_template_id',
		'layout',
		'user_id',
		'active',
		'activated_at',
	);

	/**
	 * The special typed fields for the model.
	 *
	 * @var    array
	 */
	protected static $types = array(
		'slug'         => 'unique-slug',
		'active'       => 'checkbox',
		'activated_at' => 'date-time',
	);

	/**
	 * The default values for the model.
	 *
	 * @return array
	 */
	public static function defaults()
	{
		$defaults = array(
			'active'       => true,
			'activated_at' => date(Form::getDateTimeFormat()),
		);

		$defaults = array_merge($defaults, static::addPrefixToDefaults(BlogContentArea::defaults(), 'blog_content_areas.1'));

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
						'content_areas.'.$number.'.title'            => array('required'),
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
		return Fractal::blogUrl($this->slug);
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
			->belongsToMany('Regulus\Fractal\BlogContentArea', 'blog_article_content_areas', 'article_id', 'area_id')
			->withPivot('layout_tag')
			->orderBy('title');
	}

	/**
	 * Gets the active pages.
	 *
	 * @return Collection
	 */
	public static function getActive()
	{
		return static::where('active', true)->where('activated_at', '>=', date('Y-m-d H:i:s'))->orderBy('id')->get();
	}

	/**
	 * Get the active status.
	 *
	 * @param  mixed    $dateFormat
	 * @return string
	 */
	public function getActiveStatus($dateFormat = false)
	{
		if (!$dateFormat)
			$dateFormat = Fractal::getDateTimeFormat();

		if ($this->active && Fractal::dateTimePast($this->activated_at)) {
			$status = "Yes";
		} else {
			$status = "No";

			if ($this->active && Fractal::dateTimeSet($this->activated_at))
				$status .= '<div><small><em>To be activated at '.date($dateFormat, strtotime($this->activated_at)).'</em></small></div>';
		}

		return $status;
	}

	/**
	 * Get the last updated date/time.
	 *
	 * @param  mixed    $dateFormat
	 * @return string
	 */
	public function getLastUpdatedDate($dateFormat = false)
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
	 * @return Collection
	 */
	public function getViews()
	{
		return ContentView::getViewsForItem($this);
	}

	/**
	 * Get the number of unique page views.
	 *
	 * @return Collection
	 */
	public function getUniqueViews()
	{
		return ContentView::getUniqueViewsForItem($this);
	}

}