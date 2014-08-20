<?php namespace Regulus\Fractal\Models;

use Aquanode\Formation\BaseModel;

use Fractal;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\View;

class BlogContentArea extends BaseModel {

	/**
	 * The database table used by the model.
	 *
	 * @var    string
	 */
	protected $table = 'blog_content_areas';

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
	protected $fillable = array(
		'title',
		'content_type',
		'content',
		'user_id',
	);

	/**
	 * The special typed fields for the model.
	 *
	 * @var    string
	 */
	protected $types = array(
		'updated_at' => 'date-time',
	);

	/**
	 * The default values for the model.
	 *
	 * @return array
	 */
	public static function defaults()
	{
		$defaults = array(
			'content_type' => 'Markdown',
		);

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
	 * The blog articles that the area belongs to.
	 *
	 * @return Collection
	 */
	public function articles()
	{
		return $this->belongsToMany('Regulus\Fractal\Models\BlogArticle', 'blog_article_content_areas', 'area_id', 'article_id');
	}

	/**
	 * Get the IDs of the articles.
	 *
	 * @return array
	 */
	public function getArticleIds()
	{
		$ids = array();

		foreach ($this->articles as $article) {
			$ids[] = (int) $article->id;
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

		if ($this->articles()->count()) {
			$title = $this->articles[0]->title;

			$pivotItem = DB::table('blog_article_content_areas')
				->select('layout_tag')
				->where('area_id', $this->id)
				->where('article_id', $this->articles[0]->id)
				->first();

			if (!empty($pivotItem))
				$title .= ' - '.ucwords(str_replace('-', ' ', $pivotItem->layout_tag));

			return $title;
		}

		return Lang::get('fractal::labels.untitled');
	}

	/**
	 * Get the rendered content.
	 *
	 * @param  boolean  $previewOnly
	 * @return string
	 */
	public function getRenderedContent($previewOnly = false)
	{
		return Fractal::renderContent($this->content, $this->content_type, $previewOnly);
	}

	/**
	 * Apply rendered content to layout.
	 *
	 * @param  boolean  $previewOnly
	 * @return string
	 */
	public function renderContentToLayout($content, $previewOnly = false)
	{
		return str_replace('{{'.$this->pivot->layout_tag.'}}', $this->getRenderedContent($previewOnly), $content);
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

}