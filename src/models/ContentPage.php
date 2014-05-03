<?php namespace Regulus\Fractal;

use Illuminate\Database\Eloquent\Model as Eloquent;

use Illuminate\Support\Facades\Config;

class ContentPage extends Eloquent {

	/**
	 * The database table used by the model.
	 *
	 * @var    string
	 */
	protected $table = 'content_pages';

	/**
	 * Get the validation rules used by the model.
	 *
	 * @param  boolean  $id
	 * @return string
	 */
	public static function validationRules($id = false)
	{
		return array(
			'title'   => array('required'),
			'slug'    => array('required'),
		);
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
		return $this->belongsTo('Regulus\Fractal\ContentLayoutTemplate', 'layout_template_id');
	}

	/**
	 * Get the layout for the page.
	 *
	 * @return Collection
	 */
	public function getLayout()
	{
		if ($this->layoutTemplate)
			return $this->layoutTemplate->layout;

		return $this->layout;
	}

	/**
	 * Get the rendered content for the page.
	 *
	 * @return Collection
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
			->belongsToMany('Regulus\Fractal\ContentArea', 'content_page_areas', 'page_id', 'area_id')
			->withPivot('layout_tag');
	}

	/**
	 * The menu item that the page belongs to.
	 *
	 * @return Collection
	 */
	public function menuItems()
	{
		return $this->hasMany('Regulus\Fractal\MenuItem');
	}

	/**
	 * Gets the page by its slug.
	 *
	 * @param  string   $slug
	 * @return Page
	 */
	public static function findBySlug($slug)
	{
		return static::where('slug', $slug)->first();
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