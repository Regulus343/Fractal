<?php namespace Regulus\Fractal\Models\Blogs;

use Regulus\Formation\Models\Base;

use Fractal;

class Blog extends Base {

	/**
	 * The database table used by the model.
	 *
	 * @var    string
	 */
	protected $table = 'blogs';

	/**
	 * The fillable fields for the model.
	 *
	 * @var    array
	 */
	protected $fillable = [
		'user_id',
		'slug',
	];

}