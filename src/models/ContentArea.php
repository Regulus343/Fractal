<?php namespace Regulus\Fractal;

use Illuminate\Database\Eloquent\Model as Eloquent;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;

class ContentArea extends Eloquent {

	/**
	 * The database table used by the model.
	 *
	 * @var    string
	 */
	protected $table = 'content_areas';

	/**
	 * Get the validation rules used by the model.
	 *
	 * @param  boolean  $id
	 * @return string
	 */
	public static function validationRules($id = false)
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