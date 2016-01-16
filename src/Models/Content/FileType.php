<?php namespace Regulus\Fractal\Models\Content;

use Regulus\Formation\Models\Base;

class FileType extends Base {

	/**
	 * The database table used by the model.
	 *
	 * @var    string
	 */
	protected $table = 'file_types';

	/**
	 * The foreign key for the model.
	 *
	 * @var    string
	 */
	protected $foreignKey = 'file_type_id';

	/**
	 * The fillable fields for the model.
	 *
	 * @var    array
	 */
	protected $fillable = [
		'slug',
		'name',
		'extensions',
	];

	/**
	 * The special typed fields for the model.
	 *
	 * @var    array
	 */
	protected $types = [
		'slug' => 'unique-slug',
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
	 * The media types that belong to the file type.
	 *
	 * @return Collection
	 */
	public function mediaTypes()
	{
		return $this->hasMany('Regulus\Fractal\Models\Media\Type');
	}

	/**
	 * The files that belong to the file type.
	 *
	 * @return Collection
	 */
	public function files()
	{
		return $this->hasMany('Regulus\Fractal\Models\Content\File', 'type_id');
	}

	/**
	 * The media items that belong to the file type.
	 *
	 * @return Collection
	 */
	public function mediaItems()
	{
		return $this->hasMany('Regulus\Fractal\Models\Media\Item');
	}

	/**
	 * Get the extensions for various file type IDs.
	 *
	 * @return string
	 */
	public function getExtensions()
	{
		return explode(', ', $this->extensions);
	}

	/**
	 * Check if a file type has a particular extension.
	 *
	 * @param  string   $extension
	 * @return boolean
	 */
	public function hasExtension($extension)
	{
		return in_array(strtolower($extension), $this->getExtensions());
	}

	/**
	 * Get the extensions for various file type IDs.
	 *
	 * @param  mixed    $id
	 * @return string
	 */
	public static function getExtensionsForIds($id = null)
	{
		$result = [];

		if ($id) {
			$fileType = static::find($id);
			if (!empty($fileType))
				$result = $fileType->getExtensions();
		} else {
			$fileTypes = static::orderBy('id')->get();
			foreach ($fileTypes as $fileType) {
				$result[(int) $fileType->id] = $fileType->getExtensions();
			}
		}

		return $result;
	}

	/**
	 * Get the file type ID from an extension.
	 *
	 * @param  string   $extension
	 * @return mixed
	 */
	public static function getIdFromExtension($extension)
	{
		$extension = strtolower($extension);
		$fileType  = FileType::where(function($query) use ($extension)
		{
			$query
				->orWhere('extensions', '=', $extension)
				->orWhere('extensions', 'like', $extension.',%')
				->orWhere('extensions', 'like', '%, '.$extension.',%')
				->orWhere('extensions', 'like', '%, '.$extension);
		})->first();

		if (!empty($fileType))
			return (int) $fileType->id;

		return null;
	}

}