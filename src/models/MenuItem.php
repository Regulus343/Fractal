<?php namespace Regulus\Fractal\Models;

use Aquanode\Formation\BaseModel;

use Fractal;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

use \Site as Site;
use Regulus\TetraText\TetraText as Format;

class MenuItem extends BaseModel {

	/**
	 * The database table used by the model.
	 *
	 * @var    string
	 */
	protected $table = 'menu_items';

	/**
	 * The fillable fields for the model.
	 *
	 * @var    array
	 */
	protected $fillable = array(
		'name',
		'cms',
		'menu_id',
		'parent_id',
		'type',
		'page_id',
		'uri',
		'label',
		'icon',
		'class',
		'additional_info',
		'display_order',
		'auth_status',
		'auth_roles',
		'active',
	);

	/**
	 * The special typed fields for the model.
	 *
	 * @var    array
	 */
	protected static $types = array(
		'active' => 'checkbox',
	);

	/**
	 * The menu that the menu items belong to.
	 *
	 * @return Menu
	 */
	public function menu()
	{
		return $this->belongsTo('Regulus\Fractal\Models\Menu');
	}

	/**
	 * The menu items that belong to the menu item.
	 *
	 * @return MenuItem
	 */
	public function parent()
	{
		return $this->belongsTo('Regulus\Fractal\Models\MenuItem', 'parent_id');
	}

	/**
	 * The menu items that belong to the menu item.
	 *
	 * @return Collection
	 */
	public function children()
	{
		return $this->hasMany('Regulus\Fractal\Models\MenuItem', 'parent_id')->orderBy('display_order');
	}

	/**
	 * The page that belongs to the menu item (if applicable).
	 *
	 * @return Page
	 */
	public function page()
	{
		//if (! (int) $this->page_id) return false;

		return $this->belongsTo('Regulus\Fractal\Models\ContentPage');
	}

	/**
	 * Get the visibility status of the menu item.
	 *
	 * @return boolean
	 */
	public function isVisible()
	{
		return Fractal::isMenuItemVisible($this);
	}

	/**
	 * Get the URI for the menu item.
	 *
	 * @return string
	 */
	public function getUri()
	{
		if ($this->menu->cms) {
			$uri = Config::get('fractal::baseUri');
		} else {
			$uri = '';
		}
		if ($this->page) {
			$uri = $uri != '' ? $uri.'/'.$this->page->slug : $this->page->slug;
		} else {
			$uri = $uri != '' ? $uri.'/'.$this->uri : $this->uri;
		}
		if (substr($uri, -1) == '/') $uri = substr($uri, 0, (strlen($uri) - 1));
		return $uri;
	}

	/**
	 * Get the URL for the menu item.
	 *
	 * @return string
	 */
	public function getUrl()
	{
		return URL::to($this->getUri());
	}

	/**
	 * Get the class attribute of the menu item.
	 *
	 * @param  boolean  $setSelectedClass
	 * @return string
	 */
	public function getClass($setSelectedClass = true)
	{
		$class = $this->class;

		if ($setSelectedClass)
			$class = static::setSelectedClass($this);

		return $class;
	}

	/**
	 * Get the class attribute of the menu item.
	 *
	 * @param  mixed    $item
	 * @return string
	 */
	public static function setSelectedClass($item)
	{
		if (!isset($item->parent_id)) {
			var_dump($item); exit;
		}
		$selectedClass = "active";
		$defaultClass  = $item->class;
		$class         = $defaultClass;
		$checked       = ! (int) $item->parent_id ? "section" : "subSection";

		$class .= Site::selectBy($checked, $item->label, true, $selectedClass);

		//if nothing was found and menu item is for a content page, check it's title
		if ($class == $defaultClass && $item->type == "Content Page")
			$class .= Site::selectBy($checked, $item->page, true, $selectedClass);

		return $class;
	}

	/**
	 * Get the class attribute of the menu item's anchor tag.
	 *
	 * @return string
	 */
	public function getAnchorClass()
	{
		$class = '';
		if (count($this->children)) {
			if ($class != "") $class .= ' ';
			$class = 'dropdown-toggle';
		}
		return $class;
	}

	/**
	 * Get the label of a menu item (including an icon if one exists and the icon attribute is set).
	 *
	 * @param  boolean  $icon
	 * @return string
	 */
	public function getLabel($icon = true)
	{
		$label = $this->label;

		if ($label != strip_tags($label))
			$label = Format::entities($label);

		if ($icon && $this->icon != "")
			$label = '<span class="glyphicon glyphicon-'.$this->icon.'"></span>&nbsp; '.$label;

		return $label;
	}

	/**
	 * Get a limited array of a menu item's children.
	 *
	 * @param  boolean  $setSelectedClass
	 * @param  boolean  $ignoreVisibilityStatus
	 * @return array
	 */
	public function getChildrenArray($setSelectedClass = true, $ignoreVisibilityStatus = false)
	{
		$children = array();
		foreach ($this->children as $child) {
			if ($child->isVisible() || $ignoreVisibilityStatus)
				$children[] = $child->createObject($setSelectedClass, $ignoreVisibilityStatus);
		}
		return $children;
	}

	/**
	 * Create an object for the menu item containing just the necessary data.
	 *
	 * @param  boolean  $setSelectedClass
	 * @param  boolean  $ignoreVisibilityStatus
	 * @return array
	 */
	public function createObject($setSelectedClass = true, $ignoreVisibilityStatus = false)
	{
		return (object) array(
			'parent_id'   => (int) $this->parent_id,
			'type'        => $this->type,
			'uri'         => $this->getUri(),
			'page'        => $this->type == "Content Page" ? $this->page->title : false,
			'label'       => $this->label,
			'labelIcon'   => $this->getLabel(),
			'class'       => $this->getClass($setSelectedClass),
			'active'      => $this->active,
			'auth_status' => $this->auth_status,
			'anchorClass' => $this->getAnchorClass(),
			'children'    => $this->getChildrenArray($setSelectedClass, $ignoreVisibilityStatus),
		);
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