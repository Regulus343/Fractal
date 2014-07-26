<?php namespace Regulus\Fractal\Models;

use Aquanode\Formation\BaseModel;

use Fractal;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;

use \Form as Form;

class Menu extends BaseModel {

	/**
	 * The database table used by the model.
	 *
	 * @var    string
	 */
	protected $table = 'menus';

	/**
	 * The foreign key for the model.
	 *
	 * @var    string
	 */
	protected $foreignKey = 'menu_id';

	/**
	 * The fillable fields for the model.
	 *
	 * @var    array
	 */
	protected $fillable = array(
		'name',
		'cms',
	);

	/**
	 * The special typed fields for the model.
	 *
	 * @var    array
	 */
	protected static $types = array(
		'cms' => 'checkbox',
	);

	/**
	 * Get the validation rules used by the model.
	 *
	 * @param  boolean  $id
	 * @return string
	 */
	public static function validationRules($id = false)
	{
		$rules = array(
			'name' => array('required', 'unique:menus,name'),
		);

		if ($id)
			$rules['name'][1] .= ",".$id;

		if (Form::post()) {
			foreach (Form::getValuesObject('items') as $number => $values)
			{
				if (Form::getValueFromObject('label', $values) != "")
				{
					$itemRules = array(
						'items.'.$number.'.label' => array('required'),
						'items.'.$number.'.type'  => array('required'),
					);

					$type = Form::getValueFromObject('type', $values);
					if ($type != "") {
						$contentField = $type == "URI" ? "uri" : "page_id";
						$itemRules['items.'.$number.'.'.$contentField] = array('required');
					}

					$rules = array_merge($rules, $itemRules);
				}
			}
		}

		return $rules;
	}

	/**
	 * The menu items that belong to the menu.
	 *
	 * @return Collection
	 */
	public function items()
	{
		return $this->hasMany('Regulus\Fractal\Models\MenuItem')->orderBy('parent_id')->orderBy('display_order');
	}

	/**
	 * Create a menu array.
	 *
	 * @param  boolean  $setSelectedClass
	 * @param  boolean  $ignoreVisibilityStatus
	 * @return array
	 */
	public function createArray($setSelectedClass = true, $ignoreVisibilityStatus = false)
	{
		$menuArray = array();
		foreach ($this->items as $menuItem) {
			if (! (int) $menuItem->parent_id && ($menuItem->isVisible() || $ignoreVisibilityStatus))
				$menuArray[] = $menuItem->createObject($setSelectedClass, $ignoreVisibilityStatus);
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
		if ($class != "")
			$class = ' '.$class;

		return View::make(Config::get('fractal::viewsLocation').'partials.menu')
			->with('menu', $menu)
			->with('listItemsOnly', $listItemsOnly)
			->with('class', $class)
			->render();
	}

	/**
	 * Get a menu array by name.
	 *
	 * @param  string   $name
	 * @return array
	 */
	public static function getArray($name)
	{
		$menu = Config::get('fractal::menus.'.Fractal::toCamelCase($name));
		if (!is_null($menu))
			return $menu;

		$menu = static::where('name', $name)->first();
		if ($menu)
			return $menu->createArray();

		return array();
	}

	/**
	 * Create menu markup for Bootstrap.
	 *
	 * @param  string   $name
	 * @param  boolean  $listItemsOnly
	 * @param  string   $class
	 * @return string
	 */
	public static function createMarkupForMenu($name, $listItemsOnly = false, $class = '')
	{
		$menu = (object) static::getArray($name);
		if ($class != "")
			$class = ' '.$class;

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
						$menuItems .= '<a href="'.$menuItem->getUrl().'" target="_blank">'.$menuItem->label.'</a>';
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