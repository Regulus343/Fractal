<?php namespace Regulus\Fractal\Models;

use Aquanode\Formation\BaseModel;

use Fractal;

use Illuminate\Support\Facades\Config;
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
	protected static $types = array(
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
	 * Get the validation rules used by the model.
	 *
	 * @param  mixed    $id
	 * @return array
	 */
	public static function validationRules($id = null)
	{
		return array(
			'title'   => array('required'),
			'content' => array('required'),
		);
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
	 * Get the IDs of the blog articles.
	 *
	 * @return Collection
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
	 * Get the rendered content.
	 *
	 * @return string
	 */
	public function getRenderedContent()
	{
		return Fractal::renderContent($this->content, $this->content_type);
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
	public function getLastUpdatedDate($dateFormat = false)
	{
		if (!$dateFormat)
			$dateFormat = Fractal::getDateTimeFormat();

		return Fractal::dateTimeSet($this->updated_at) ? date($dateFormat, strtotime($this->updated_at)) : date($dateFormat, strtotime($this->created_at));
	}

}