<?php namespace Regulus\Fractal\Models\Content;

use Regulus\Formation\Models\Base;

use Fractal;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;

use Auth;
use Form;
use Format;
use Site;

use Regulus\Fractal\Models\Content\View as ContentView;

use Regulus\Fractal\Traits\Publishable;

use AlfredoRamos\ParsedownExtra\Facades\ParsedownExtra as Markdown;

class Page extends Base {

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
		'content_rendered',
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
		'sticky'       => 'checkbox',
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
		'published_at' => 'null-if-blank',
	];

	/**
	 * The restricted slugs for the model.
	 *
	 * @var    array
	 */
	protected static $restrictedSlugs = [
		'admin',
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

		$defaults = array_merge($defaults, static::addPrefixToDefaults(Area::defaults(), 'content_areas.1'));

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
		$rules = [
			'slug'  => ['required', 'lowercase_not_in:'.implode(',', static::$restrictedSlugs)],
			'title' => ['required'],
		];

		if (Form::post())
		{
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
		return $this->belongsTo('Regulus\Fractal\Models\Content\LayoutTemplate', 'layout_template_id');
	}

	/**
	 * The content areas that the page belongs to.
	 *
	 * @return Collection
	 */
	public function contentAreas()
	{
		return $this
			->belongsToMany('Regulus\Fractal\Models\Content\Area', 'content_page_areas', 'page_id', 'area_id')
			->withPivot('layout_tag')
			->orderBy('title');
	}

	/**
	 * Get the Markdown-formatted title of the page.
	 *
	 * @return string
	 */
	public function getTitle()
	{
		return Markdown::line($this->title);
	}

	/**
	 * Get the URL for the page.
	 *
	 * @return string
	 */
	public function getUrl()
	{
		return Site::url(Config::get('fractal::pageUri').'/'.$this->slug);
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
	 * @param  boolean  $render
	 * @return string
	 */
	public function getRenderedContent($render = false)
	{
		// return pre-rendered content if "render" is not set
		if (!$render && !is_null($this->content_rendered))
		{
			$content = Fractal::renderContentViews($this->content_rendered);
		}
		else
		{
			$content = $this->getLayout();

			foreach ($this->contentAreas as $contentArea) {
				$content = $contentArea->renderContentToLayout($content);
			}

			$this->content_rendered = $content;
			$this->save();
		}

		return $this->addBreadcrumbTrailToContent($content);
	}

	/**
	 * Render the content for the page.
	 *
	 * @return string
	 */
	public function renderContent($render = false)
	{
		return $this->getRenderedContent(true);
	}

	/**
	 * Add the breadcrumb trail to content if necessary.
	 *
	 * @return string
	 */
	public function addBreadcrumbTrailToContent($content)
	{
		$html = Site::getBreadcrumbTrailMarkup();

		return str_replace('[breadcrumb-trail]', $html, str_replace('<p>[breadcrumb-trail]</p>', $html, $content));
	}

	/**
	 * The menu item that the page belongs to.
	 *
	 * @return Collection
	 */
	public function menuItems()
	{
		return $this->hasMany('Regulus\Fractal\Models\Content\MenuItem');
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
	 * @param  boolean  $allowAdmin
	 * @return Collection
	 */
	public function scopeOnlyPublished($query, $allowAdmin = false)
	{
		if ($allowAdmin && Auth::is('admin'))
			return $query;

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
	 * Check whether page is to be published in the future.
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

		Fractal::setRequestedPage();

		return $pages->paginate($searchData['itemsPerPage']);
	}

}