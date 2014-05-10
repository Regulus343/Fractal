<?php namespace Regulus\Fractal;

use Illuminate\Database\Eloquent\Model as Eloquent;

use Illuminate\Support\Facades\Config;

use Aquanode\Formation\Formation as Form;

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
	 * @return string
	 */
	public static function validationRules()
	{
		$rules = array(
			'title' => array('required'),
			'slug'  => array('required'),
		);

		if (Form::post()) {
			foreach (Form::getValuesObject('content_areas') as $number => $values) {
				if (Form::getValueFromObject('title', $values) != "" || Form::getValueFromObject('layout_tag', $values) != ""
				|| Form::getValueFromObject('content_html', $values) != "" || Form::getValueFromObject('content_markdown', $values) != "")
				{
					$contentField = Form::getValueFromObject('content_type', $values) == "HTML" ? "content_html" : "content_markdown";

					$rules = array_merge($rules, array(
						'content_areas.'.$number.'.title'          => array('required'),
						'content_areas.'.$number.'.layout_tag'     => array('required'),
						'content_areas.'.$number.'.content_type'   => array('required'),
						'content_areas.'.$number.'.'.$contentField => array('required'),
					));
				}
			}
		}

		return $rules;
	}

	/**
	 * Get the validation rules used by the model.
	 *
	 * @param  boolean  $id
	 * @return string
	 */
	public function validationRulesForItem($id = false)
	{
		$rules = array(
			'title' => array('required'),
			'slug'  => array('required'),
		);

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
	 * Gets the active pages.
	 *
	 * @return Collection
	 */
	public static function getActive()
	{
		return static::where('active', true)->where('activated_at', '>=', date('Y-m-d H:i:s'))->orderBy('id')->get();
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