<?php namespace Regulus\Fractal\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

use Fractal;

use Illuminate\Support\Facades\Config;

class ContentLayoutTemplate extends Eloquent {

	/**
	 * The database table used by the model.
	 *
	 * @var    string
	 */
	protected $table = 'content_layout_templates';

	/**
	 * Get the validation rules used by the model.
	 *
	 * @param  boolean  $id
	 * @return string
	 */
	public static function validationRules($id = false)
	{
		return [
			'name'   => ['required'],
			'layout' => ['required'],
		];
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
	public function pages()
	{
		return $this->hasMany('Regulus\Fractal\ContentPage', 'layout_template_id');
	}

	/**
	 * Get the last updated date/time.
	 *
	 * @param  mixed    $dateFormat
	 * @return string
	 */
	public function getLastUpdatedDateTime($dateFormat = false)
	{
		if (!$dateFormat) $dateFormat = Config::get('fractal::dateTimeFormat');
		return $this->updated_at != "0000-00-00" ? date($dateFormat, strtotime($this->updated_at)) : date($dateFormat, strtotime($this->created_at));
	}

}