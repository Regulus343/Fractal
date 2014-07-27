<?php namespace Regulus\Fractal\Models;

use Aquanode\Formation\BaseModel;

use Fractal;

use Illuminate\Support\Facades\Config;

use \Site as Site;

class ContentFile extends BaseModel {

	/**
	 * The database table used by the model.
	 *
	 * @var    string
	 */
	protected $table = 'content_files';

	/**
	 * Get the validation rules used by the model.
	 *
	 * @param  boolean  $id
	 * @return string
	 */
	public static function validationRules($id = null)
	{
		$rules = array(
			'name' => array('required'),
		);

		if (!$id)
			$rules['file'] = array('required');

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
	 * Get files for a specific extension.
	 *
	 * @param  string   $extension
	 * @return Page
	 */
	public static function getForExtension($extension)
	{
		return static::where('extension', $extension)->orderBy('name')->get();
	}

	/**
	 * Get the path of the file.
	 *
	 * @param  boolean  $thumbnail
	 * @return string
	 */
	public function getPath($thumbnail = false)
	{
		$path = ($thumbnail && $this->thumbnail ? 'thumbnails/' : '').$this->filename;

		if ($this->path != "")
			$path = $this->path.'/'.$path;

		return $path;
	}

	/**
	 * Get the URL of the file.
	 *
	 * @param  boolean  $thumbnail
	 * @return string
	 */
	public function getUrl($thumbnail = false)
	{
		return Site::uploadedFile($this->getPath($thumbnail));
	}

	/**
	 * Get the image URL or a placeholder image URL for the file.
	 *
	 * @param  boolean  $thumbnail
	 * @return string
	 */
	public function getImageUrl($thumbnail = false)
	{
		if ($this->type == "Image")
			return $this->getUrl($thumbnail);
		else
			return Site::img('image-not-available', 'regulus/fractal');
	}

	/**
	 * Get the image or a placeholder image for the file.
	 *
	 * @param  boolean  $thumbnail
	 * @return string
	 */
	public function getImage($thumbnail = false)
	{
		return '<a href="'.$this->getUrl().'" target="_blank"><img src="'.$this->getImageUrl($thumbnail).'" alt="'.$this->name.'" title="'.$this->name.'" /></a>';
	}

	/**
	 * Get the thumbnail image or a placeholder image for the file.
	 *
	 * @return string
	 */
	public function getThumbnailImage()
	{
		return $this->getImage(true);
	}

	/**
	 * Get the image dimensions of the file and the thumbnail if it exists.
	 *
	 * @param  boolean  $thumbnail
	 * @return string
	 */
	public function getImageDimensions($thumbnail = false)
	{
		if ($this->type != "Image")
			return "&ndash;";

		$dimensions = '<div>'.$this->width.' x '.$this->height.'</div>';
		if ($this->thumbnail)
			$dimensions .= '<div>'.$this->thumbnail_width.' x '.$this->thumbnail_height.'</div>';

		return $dimensions;
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