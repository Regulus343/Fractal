<?php namespace Regulus\Fractal\Models\Record;

use Regulus\Formation\Models\Base;

class Form extends Base {

	/**
	 * The database table used by the model.
	 *
	 * @var    string
	 */
	protected $table = 'form_records';

	/**
	 * The foreign key for the model.
	 *
	 * @var    string
	 */
	protected $foreignKey = 'record_id';

	/**
	 * The fillable fields for the model.
	 *
	 * @var    array
	 */
	protected $fillable = [
		'form_id',
		'user_id',
		'first_name',
		'last_name',
		'email',
		'data',
		'processed_at',
	];

	/**
	 * The special typed fields for the model.
	 *
	 * @var    array
	 */
	protected $types = [
		'processed_at' => 'date-time',
	];

	/**
	 * The special formatted fields for the model for saving to the database.
	 *
	 * @var    array
	 */
	protected $formatsForDb = [
		'processed_at' => 'null-if-blank',
	];

	/**
	 * Get the validation rules used by the model.
	 *
	 * @param  mixed    $id
	 * @return string
	 */
	public static function validationRules($id = null)
	{
		return [
			'data' => ['required'],
		];
	}

	/**
	 * The form that the form record belongs to.
	 *
	 * @return Regulus\Fractal\Models\Content\Form
	 */
	public function form()
	{
		return $this->belongsTo('Regulus\Fractal\Models\Content\Form');
	}

	/**
	 * The user that the form record belongs to.
	 *
	 * @return Regulus\Fractal\Models\User\User
	 */
	public function user()
	{
		return $this->belongsTo('Regulus\Fractal\Models\User\User');
	}

}