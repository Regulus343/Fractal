<?php namespace Regulus\Fractal\Models\Blog;

use Regulus\Formation\Models\Base;

use Fractal;

use Auth;
use Form;
use Format;
use Site;

use Regulus\Fractal\Models\Content\LayoutTemplate;
use Regulus\Fractal\Models\Content\View as ContentView;

use Regulus\Fractal\Traits\Publishable;

use AlfredoRamos\ParsedownExtra\Facades\ParsedownExtra as Markdown;

class Article extends Base {

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
	protected $fillable = [
		'blog_id',
		'user_id',
		'slug',
		'title',
		'layout_template_id',
		'layout',
		'content_rendered',
		'content_rendered_preview',
		'thumbnail_image_type',
		'thumbnail_image_file_id',
		'thumbnail_image_media_item_id',
		'audio_file',
		'comments_enabled',
		'published_at',
	];

	/**
	 * The special typed fields for the model.
	 *
	 * @var    array
	 */
	protected $types = [
		'slug'             => 'unique-slug',
		'comments_enabled' => 'checkbox',
		'published_at'     => 'date-time',
	];

	/**
	 * The special formatted fields for the model.
	 *
	 * @var    array
	 */
	protected $formats = [
		'published'  => 'trueIfNotNull:published_at',
	];

	/**
	 * The special formatted fields for the model for saving to the database.
	 *
	 * @var    array
	 */
	protected $formatsForDb = [
		'thumbnail_image_type'          => 'nullIfBlank',
		'thumbnail_image_file_id'       => 'nullIfBlank',
		'thumbnail_image_media_item_id' => 'nullIfBlank',
		'categories'                    => 'pivotArray',
		'published_at'                  => 'nullIfBlank',
	];

	/**
	 * The restricted slugs for the model.
	 *
	 * @var    array
	 */
	protected static $restrictedSlugs = [
		'a',
		'admin',
		'article',
		'c',
		'category',
		'p',
		'page',
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
			'comments_enabled'   => Fractal::getSetting('Enable Article Comments', true),
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
	 * The blog that the article belongs to.
	 *
	 * @return Blog
	 */
	public function blog()
	{
		return $this->belongsTo('Regulus\Fractal\Models\Blog\Blog');
	}

	/**
	 * The author of the article.
	 *
	 * @return User
	 */
	public function author()
	{
		return $this->belongsTo(config('auth.model'), 'user_id');
	}

	/**
	 * The layout template that the article belongs to.
	 *
	 * @return LayoutTemplate
	 */
	public function layoutTemplate()
	{
		return $this->belongsTo('Regulus\Fractal\Models\Content\LayoutTemplate', 'layout_template_id');
	}

	/**
	 * The thumbnail image content file.
	 *
	 * @return File
	 */
	public function thumbnailImageFile()
	{
		return $this->belongsTo('Regulus\Fractal\Models\Content\File', 'thumbnail_image_file_id');
	}

	/**
	 * The thumbnail image media item.
	 *
	 * @return Item
	 */
	public function thumbnailImageMediaItem()
	{
		return $this->belongsTo('Regulus\Fractal\Models\Media\Item', 'thumbnail_image_media_item_id');
	}

	/**
	 * The content areas that the article belongs to.
	 *
	 * @return Collection
	 */
	public function contentAreas()
	{
		return $this
			->belongsToMany('Regulus\Fractal\Models\Blog\ContentArea', 'blog_article_content_areas', 'article_id', 'area_id')
			->withPivot('layout_tag')
			->orderBy('title');
	}

	/**
	 * The categories that the article belongs to.
	 *
	 * @return Collection
	 */
	public function categories()
	{
		return $this
			->belongsToMany('Regulus\Fractal\Models\Blog\Category', 'blog_article_categories', 'article_id', 'category_id')
			->orderBy('name');
	}

	/**
	 * Get the Markdown-formatted title of the article.
	 *
	 * @return string
	 */
	public function getTitle()
	{
		return Markdown::line($this->title);
	}

	/**
	 * Get the URL for the article.
	 *
	 * @return string
	 */
	public function getUrl()
	{
		return Fractal::blogUrl((!config('blogs.short_routes') ? 'a/' : '').$this->slug);
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
	 * Get the thumbnail image URL or a placeholder image URL for the article.
	 *
	 * @return string
	 */
	public function getThumbnailImageUrl()
	{
		$image            = null;
		$imagePlaceholder = Fractal::getImageUrlFromConfig('blogs.placeholder_thumbnail_image');

		if ($this->thumbnail_image_type == "File" && $this->thumbnailImageFile)
			$image = $this->thumbnailImageFile->getImageUrl(true);

		if ($this->thumbnail_image_type == "Media Item" && $this->thumbnailImageMediaItem)
			$image = $this->thumbnailImageMediaItem->getImageUrl(true);

		if (is_null($image) || $image == Fractal::getImageUrlFromConfig('cms.placeholder_image'))
			$image = $imagePlaceholder;

		return $image;
	}

	/**
	 * Check whether article has a thumbnail image.
	 *
	 * @return boolean
	 */
	public function hasThumbnailImage()
	{
		if ($this->thumbnail_image_type == "File" && $this->thumbnailImageFile)
			return true;

		if ($this->thumbnail_image_type == "Media Item" && $this->thumbnailImageMediaItem)
			return true;

		return false;
	}

	/**
	 * Get the thumbnail image or a placeholder image for the article.
	 *
	 * @return string
	 */
	public function getThumbnailImage()
	{
		return '<a href="'.$this->getUrl().'" class="thumbnail-image article-thumbnail-image"><img src="'.$this->getThumbnailImageUrl().'" alt="'.$this->title.'" title="'.$this->title.'" /></a>';
	}

	/**
	 * Get the rendered content for the article.
	 *
	 * @param  array    $config
	 * @return string
	 */
	public function getRenderedContent($config = [])
	{
		$configDefault = [
			'contentUrl'            => $this->getUrl(),
			'contentType'           => $this->content_type,
			'previewOnly'           => false,
			'render'                => true,
			'thumbnailImageFileId'  => $this->thumbnail_image_file_id,
			'viewButton'            => true,
			'viewButtonPlaceholder' => true,
			'viewButtonLabel'       => 'read_more',
		];

		$config = array_merge($configDefault, $config);

		// return pre-rendered content if "render" is not set
		if (!$config['render'])
		{
			if ($config['previewOnly'])
			{
				if (!is_null($this->content_rendered_preview))
					return Fractal::addViewButtonToContent($this->content_rendered_preview, $config);
			}
			else
			{
				if (!is_null($this->content_rendered))
					return $this->content_rendered;
			}
		}

		$content = $this->getLayout();

		// if preview only is set, select only the main or primary content area for display
		$contentAreas = $this->contentAreas;
		if ($config['previewOnly'] && config('blogs.use_standard_layout_for_article_list'))
		{
			$mainTags       = ['main', 'primary'];
			$previewDivider = config('blogs.preview_divider');

			foreach ($contentAreas as $contentArea)
			{
				if (in_array($contentArea->pivot->layout_tag, $mainTags))
				{
					$contentArea->pivot->layout_tag = "main";

					$contentArea->content_modified = $contentArea->content;

					//remove thumbnail image reference
					preg_match_all('/\[thumbnail\-image[\;A-Za-z0-9\-\.\#\ ]*\]/', $contentArea->content_modified, $thumbnailImages);
					if (isset($thumbnailImages[0]) && !empty($thumbnailImages[0]))
						$contentArea->content_modified = str_replace($thumbnailImages[0][0], '', $contentArea->content_modified);

					$contentAreas = [$contentArea];
				}
			}

			$content = '<div class="row"><div class="col-md-12">{{main}}</div></div>';

			if ((boolean) Fractal::getSetting('Display Thumbnail Images on Article List', true)
			&& ($this->hasThumbnailImage() || (boolean) Fractal::getSetting('Display Placeholder Thumbnail Images on Article List', true)))
			{
				$layoutTemplate = LayoutTemplate::find(4); //layout template: "Standard with Image"
				if (!empty($layoutTemplate))
				{
					$content = $layoutTemplate->layout;
					$content = str_replace('{{image}}', $this->getThumbnailImage(), $content);
				}
			} else {
				$layoutTemplate = LayoutTemplate::find(1); //layout template: "Standard"
				if (!empty($layoutTemplate))
					$content = $layoutTemplate->layout;
			}
		}

		foreach ($contentAreas as $contentArea) {
			$content = $contentArea->renderContentToLayout($content, $config);
		}

		if ($config['previewOnly'])
			$this->content_rendered_preview = Fractal::addViewButtonToContent($content, $config);
		else
			$this->content_rendered = $content;

		$this->save();

		return $content;
	}

	/**
	 * Rendered the content for the article.
	 *
	 * @param  array    $config
	 * @return string
	 */
	public function renderContent($config = [])
	{
		$config['render'] = true;

		return $this->getRenderedContent($config);
	}

	/**
	 * Check whether comments are enabled for article.
	 *
	 * @return boolean
	 */
	public function commentsEnabled()
	{
		return Fractal::getSetting('Enable Article Comments', true) && $this->comments_enabled;
	}

	/**
	 * Gets the published articles.
	 *
	 * @return Collection
	 */
	public static function getPublished()
	{
		return static::onlyPublished()->orderBy('id')->get();
	}

	/**
	 * Gets only the published articles.
	 *
	 * @param  boolean  $allowAdmin
	 * @return Collection
	 */
	public function scopeOnlyPublished($query, $allowAdmin = false)
	{
		if ($allowAdmin && Auth::is('admin'))
			return $query;

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

	/**
	 * Get article search results.
	 *
	 * @param  array    $searchData
	 * @return Collection
	 */
	public static function getSearchResults($searchData)
	{
		$articles = static::select(['blog_articles.*'])
			->leftJoin('blog_article_categories', 'blog_articles.id', '=', 'blog_article_categories.article_id')
			->groupBy('blog_articles.id')
			->orderBy($searchData['sortField'], $searchData['sortOrder']);

		if ($searchData['sortField'] != "id")
			$articles->orderBy('id', 'asc');

		if ($searchData['terms'] != "")
			$articles->where(function($query) use ($searchData) {
				$query
					->where('title', 'like', $searchData['likeTerms'])
					->orWhere('slug', 'like', $searchData['likeTerms']);
			});

		$filters = $searchData['filters'];
		if (!empty($filters))
		{
			$allowedFilters = [
				'category_id' => 'blog_article_categories.category_id',
			];

			foreach ($allowedFilters as $allowedFilter => $filterField)
			{
				if (isset($filters[$allowedFilter]) && $filters[$allowedFilter] && $filters[$allowedFilter] != "")
					$articles->where($filterField, $filters[$allowedFilter]);
			}
		}

		return $articles->paginate($searchData['itemsPerPage']);
	}

}