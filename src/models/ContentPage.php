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
	 * The creator of the page.
	 *
	 * @return User
	 */
	public function creator()
	{
		return $this->belongsTo(Config::get('auth.model'), 'user_id');
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

	/**
	 * Get the validation rules.
	 *
	 * @param  boolean  $update
	 * @return string
	 */
	public static function validationRules($update = false)
	{
		return array(
			'title'   => array('required'),
			'slug'    => array('required'),
			'content' => array('required'),
		);
	}

}