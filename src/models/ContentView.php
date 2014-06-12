<?php namespace Regulus\Fractal;

use Aquanode\Formation\BaseModel;

class ContentView extends BaseModel {

	/**
	 * The database table used by the model.
	 *
	 * @var    string
	 */
	protected $table = 'content_views';

	/**
	 * The foreign key for the model.
	 *
	 * @var    string
	 */
	protected $foreignKey = 'view_id';

	/**
	 * The fillable fields for the model.
	 *
	 * @var    array
	 */
	protected $fillable = array(
		'user_id',
		'content_id',
		'content_type',
		'ip_address',
		'user_agent',
	);

	/**
	 * The user of the view if one exists.
	 *
	 * @return User
	 */
	public function user()
	{
		return $this->belongsTo(Config::get('auth.model'), 'user_id');
	}

}