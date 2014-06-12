<?php namespace Regulus\Fractal;

use Aquanode\Formation\BaseModel;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;

class ContentArea extends BaseModel {

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
	 * The content pages that the area belongs to.
	 *
	 * @return Collection
	 */
	public function contentPages()
	{
		return $this->belongsToMany('Regulus\Fractal\ContentPage', 'content_page_areas', 'area_id', 'page_id');
	}

	/**
	 * Get the IDs of the content pages.
	 *
	 * @return Collection
	 */
	public function getContentPageIds()
	{
		$ids = array();

		foreach ($this->contentPages as $contentPage) {
			$ids[] = (int) $contentPage->id;
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
		if (!$dateFormat) $dateFormat = Config::get('fractal::dateTimeFormat');
		return $this->updated_at != "0000-00-00" ? date($dateFormat, strtotime($this->updated_at)) : date($dateFormat, strtotime($this->created_at));
	}

}