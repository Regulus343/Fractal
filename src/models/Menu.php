<?php namespace Regulus\Fractal;

use Illuminate\Database\Eloquent\Model as Eloquent;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;

class Menu extends Eloquent {

	/**
	 * The database table used by the model.
	 *
	 * @var    string
	 */
	protected $table = 'menus';

	/**
	 * The form setup for the model.
	 *
	 * @var    string
	 */
	public static $form = array(
		'name' => array(
			'type'      => 'text',
			'rules'     => array('required'),
		),
		'cms' => array(
			'type'      => 'checkbox',
			'label'     => 'CMS',
			'authRoles' => 'admin',
		),
	);

	/**
	 * The menu items that belong to the menu.
	 *
	 * @return Collection
	 */
	public function items()
	{
		return $this->hasMany('Regulus\Fractal\MenuItem')->orderBy('display_order');
	}

	/**
	 * Create a menu array.
	 *
	 * @return array
	 */
	public function createArray()
	{
		$menuArray = array();
		foreach ($this->items as $menuItem) {
			if (! (int) $menuItem->parent_id && $menuItem->isVisible()) {
				$menuArray[] = (object) array(
					'uri'         => $menuItem->getURI(),
					'label'       => $menuItem->getLabel(),
					'class'       => $menuItem->getClass(),
					'anchorClass' => $menuItem->getAnchorClass(),
					'children'    => $menuItem->getChildrenArray(),
				);
			}
		}
		return $menuArray;
	}

	/**
	 * Create menu markup for Bootstrap.
	 *
	 * @param  boolean  $listItemsOnly
	 * @param  string   $class
	 * @return string
	 */
	public function createMarkup($listItemsOnly = false, $class = '')
	{
		$menu = $this->createArray();
		if ($class != "") $class = ' '.$class;

		return View::make(Config::get('fractal::viewsLocation').'partials.menu')
			->with('menu', $menu)
			->with('listItemsOnly', $listItemsOnly)
			->with('class', $class)
			->render();
	}

	/**
	 * Create a string of active parent menu items.
	 *
	 * @return string
	 */
	public function getActiveItemPreview()
	{
		$menuItems = '';
		$added     = 0;
		$complete  = false;
		foreach ($this->items as $menuItem) {
			if (!$complete) {
				if (! (int) $menuItem->parent_id && $menuItem->active) {
					if ($added < 3) {
						if ($menuItems != "") $menuItems .= ', ';
						$menuItems .= '<a href="'.$menuItem->getURL().'" target="_blank">'.$menuItem->label.'</a>';
						$added ++;
					} else {
						$menuItems .= '...';
					}
				}
			}
		}
		return $menuItems;
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