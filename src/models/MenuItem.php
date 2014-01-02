<?php namespace Regulus\Fractal;

use Illuminate\Database\Eloquent\Model as Eloquent;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

use Regulus\SolidSite\SolidSite as Site;
use Regulus\TetraText\TetraText as Format;

class MenuItem extends Eloquent {

	/**
	 * The database table used by the model.
	 *
	 * @var    string
	 */
	protected $table = 'menu_items';

	/**
	 * The form setup for the model.
	 *
	 * @var    string
	 */
	public static $form = array(
		'menu_id' => array(
			'type'       => 'select',
			'options'    => 'menuOptions',
			'nullOption' => 'Select a menu',
			'label'      => 'Menu',
			'rules'      => array('required'),
		),
		'parent_id' => array(
			'type'       => 'select',
			'options'    => 'menuItemOptions',
			'nullOption' => 'Select a parent',
			'label'      => 'Parent Menu Item',
		),
		'page_id' => array(
			'type'       => 'select',
			'options'    => 'pageOptions',
			'nullOption' => 'Select a page',
			'label'      => 'Parent Menu Item',
		),
		'uri' => array(
			'type'       => 'text',
			'label'      => 'URI',
		),
		'label' => array(
			'type'       => 'text',
			'rules'      => array('required'),
		),
		'class' => array(
			'type'       => 'text',
		),
		'display_order' => array(
			'type'       => 'select',
			'options'    => 'displayOrderOptions',
		),
		'auth_status' => array(
			'type'       => 'select',
			'options'    => array(
				'All',
				'Logged In',
				'Logged Out',
			),
			'label'      => 'Authorization Status',
		),
		'auth_roles' => array(
			'type'       => 'checkbox-set',
			'options'    => 'authRoleOptions',
			'label'      => 'Authorization Roles',
		),
		'active' => array(
			'type'       => 'checkbox',
		),
	);

	/**
	 * The menu that the menu items belong to.
	 *
	 * @return Menu
	 */
	public function menu()
	{
		return $this->belongsTo('Regulus\Fractal\Menu');
	}

	/**
	 * The menu items that belong to the menu item.
	 *
	 * @return MenuItem
	 */
	public function parent()
	{
		return $this->belongsTo('Regulus\Fractal\MenuItem', 'parent_id');
	}

	/**
	 * The menu items that belong to the menu item.
	 *
	 * @return Collection
	 */
	public function children()
	{
		return $this->hasMany('Regulus\Fractal\MenuItem', 'parent_id')->orderBy('display_order');
	}

	/**
	 * The page that belongs to the menu item (if applicable).
	 *
	 * @return Page
	 */
	public function page()
	{
		//if (! (int) $this->page_id) return false;

		return $this->belongsTo('Regulus\Fractal\ContentPage');
	}

	/**
	 * Get the display status of the menu item.
	 *
	 * @return string
	 */
	public function isVisible()
	{
		$visible = true;

		if (!$this->active) $visible = false;

		if ($this->auth_status) {
			if (Fractal::auth() && (int) $this->auth_status == 2)
				$visible = false;

			if (!Fractal::auth() && (int) $this->auth_status == 1)
				$visible = false;
		}

		return $visible;
	}

	/**
	 * Get the URI for the menu item.
	 *
	 * @return string
	 */
	public function getURI()
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
	public function getURL()
	{
		return URL::to($this->getURI());
	}

	/**
	 * Get the class attribute of the menu item.
	 *
	 * @return string
	 */
	public function getClass($selectedClass = 'active')
	{
		$class = $this->class;
		if (! (int) $this->parent_id) {
			$class .= Site::selectBy('section', $this->label, true, $selectedClass);
		} else {
			$class .= Site::selectBy('subSection', $this->label, true, $selectedClass);
		}

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
	 * @return array
	 */
	public function getChildrenArray()
	{
		$children = array();
		foreach ($this->children as $child) {
			if ($child->isVisible()) {
				$children[] = (object) array(
					'uri'         => $child->getURI(),
					'label'       => $child->getLabel(),
					'class'       => $child->getClass(),
					'anchorClass' => $child->getAnchorClass(),
					'children'    => $child->getChildrenArray(),
				);
			}
		}
		return $children;
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