<?php namespace Regulus\Fractal;

use Illuminate\Database\Eloquent\Model as Eloquent;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;

use Aquanode\Formation\BaseModel;

class ContentArea extends BaseModel {

	/**
	 * The database table used by the model.
	 *
	 * @var    string
	 */
	protected $table = 'content_areas';

	/**
	 * The foreign key for the model.
	 *
	 * @var    string
	 */
	protected $foreignKey = 'area_id';

	/**
	 * The fillable fields for the model.
	 *
	 * @var    array
	 */
	protected $fillable = array(
		'title',
		'content_type',
		'content',
		'user_id',
	);

	/**
	 * The special typed fields for the model.
	 *
	 * @var    string
	 */
	protected static $types = array(
		'updated_at' => 'date-time',
	);

	/**
	 * The default values for the model.
	 *
	 * @return array
	 */
	public static function defaults()
	{
		$defaults = array(
			'content_type' => 'Markdown',
		);

		return static::addPrefixToDefaults($defaults);
	}

	/**
	 * Get the validation rules used by the model.
	 *
	 * @param  mixed    $id
	 * @return array
	 */
	public static function validationRules($id = null)
	{
		return array(
			'title'   => array('required'),
			'content' => array('required'),
		);
	}

	/**
	 * The creator of the content area.
	 *
	 * @return User
	 */
	public function creator()
	{
		return $this->belongsTo(Config::get('auth.model'), 'user_id');
	}

	/**
	 * The content areas that the page belongs to.
	 *
	 * @return Collection
	 */
	public function contentPages()
	{
		return $this->belongsToMany('Regulus\Fractal\ContentPage', 'content_page_areas', 'page_id', 'area_id');
	}

	/**
	 * Get the rendered content.
	 *
	 * @return string
	 */
	public function getRenderedContent()
	{
		$content = $this->content;

		//add file URLs to content
		preg_match_all('/file:([0-9]*)/', $content, $fileIds);
		if (isset($fileIds[0]) && !empty($fileIds[0])) {
			$files = ContentFile::whereIn('id', $fileIds[1])->get();

			for ($f = 0; $f < count($fileIds[0]); $f++) {
				$fileUrl = "";
				foreach ($files as $file) {
					if ((int) $file->id == (int) $fileIds[1][$f])
						$fileUrl = $file->getUrl();
				}

				$content = str_replace($fileIds[0][$f], $fileUrl, $content);
			}
		}

		//render to Markdown
		if ($this->content_type == "Markdown")
			$content = "<div>Testing Markdown</div>";

		//render views in content
		preg_match_all('/\[view:\"([a-z\:\.\_\-]*)\"\]/', $content, $views);
		if (isset($views[0]) && !empty($views[0])) {
			for ($v = 0; $v < count($views[0]); $v++) {
				$view    = View::make($views[1][$v])->render();
				$content = str_replace($views[0][$v], $view, $content);
			}
		}

		return $content;
	}

	/**
	 * Apply rendered content to layout.
	 *
	 * @return string
	 */
	public function renderContentToLayout($content)
	{
		return str_replace(':'.$this->pivot->layout_tag, $this->getRenderedContent(), $content);
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