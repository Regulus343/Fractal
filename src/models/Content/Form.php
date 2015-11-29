<?php namespace Regulus\Fractal\Models\Content;

use Regulus\Formation\Models\Base;

class Form extends Base {

	/**
	 * The database table used by the model.
	 *
	 * @var    string
	 */
	protected $table = 'forms';

	/**
	 * The foreign key for the model.
	 *
	 * @var    string
	 */
	protected $foreignKey = 'form_id';

	/**
	 * The fillable fields for the model.
	 *
	 * @var    array
	 */
	protected $fillable = [
		'slug',
		'name',
		'fields',
		'save_records',
		'mail_records',
		'mail_records_address',
		'mail_confirmation',
		'activated_at',
		'deactivated_at',
	];

	/**
	 * The special typed fields for the model.
	 *
	 * @var    array
	 */
	protected $types = [
		'slug'           => 'unique-slug',
		'activated_at'   => 'date-time',
		'deactivated_at' => 'date-time',
	];

	/**
	 * The special formatted fields for the model for saving to the database.
	 *
	 * @var    array
	 */
	protected $formatsForDb = [
		'activated_at'   => 'null-if-blank',
		'deactivated_at' => 'null-if-blank',
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
			'slug' => ['required'],
			'name' => ['required'],
		];
	}

	/**
	 * The form records that belong to the form.
	 *
	 * @return Collection
	 */
	public function records()
	{
		return $this->hasMany('Regulus\Fractal\Models\Record\Form');
	}

}