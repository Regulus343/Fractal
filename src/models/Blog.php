<?php namespace Regulus\Fractal\Models;

use Regulus\Formation\BaseModel;

use Fractal;

class Blog extends BaseModel {

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