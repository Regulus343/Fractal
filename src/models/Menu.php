<?php namespace Regulus\Fractal\Models;

use Regulus\Formation\BaseModel;

use Fractal;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;

use \Form;

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
	protected $types = array(
		'cms' => 'checkbox',
	);

	/**
	 * The default menu markup options.
	 *
	 * @var    array
	 */
	public static $markupOptions = array(
		'class'                 => 'nav navbar-nav',
		'listItemsOnly'         => false,
		'actionSubMenuDropDown' => true,
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
						if ($contentField = $type == "URI") {
							if (Form::getValueFromObject('subdomain', $values) == "")
								$itemRules['items.'.$number.'.uri'] = array('required');
							else
								$itemRules['items.'.$number.'.subdomain'] = array('required');
						} else {
							$itemRules['items.'.$number.'.page_id'] = array('required');
						}
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
	 * @param  array    $options
	 * @return string
	 */
	public function createMarkup($options = array())
	{
		$options = array_merge(static::$markupOptions, $options);

		$menu = $this->createArray();

		return View::make(Config::get('fractal::viewsLocation').'partials.menu')
			->with('menu', $menu)
			->with('listItemsOnly', $options['listItemsOnly'])
			->with('class', $options['class'])
			->with('actionSubMenuDropDown', $options['actionSubMenuDropDown'])
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
	 * @param  array    $options
	 * @return string
	 */
	public static function createMarkupForMenu($name, $options = array())
	{
		$menu    = (object) static::getArray($name);
		$options = array_merge(static::$markupOptions, $options);

		return View::make(Config::get('fractal::viewsLocation').'partials.menu')
			->with('menu', $menu)
			->with('listItemsOnly', $options['listItemsOnly'])
			->with('class', $options['class'])
			->with('actionSubMenuDropDown', $options['actionSubMenuDropDown'])
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
	public function getLastUpdatedDateTime($dateFormat = false)
	{
		if (!$dateFormat) $dateFormat = Config::get('fractal::dateTimeFormat');
		return $this->updated_at != "0000-00-00" ? date($dateFormat, strtotime($this->updated_at)) : date($dateFormat, strtotime($this->created_at));
	}

	/**
	 * Get menu search results.
	 *
	 * @param  array    $searchData
	 * @return Collection
	 */
	public static function getSearchResults($searchData)
	{
		$menus = static::orderBy($searchData['sortField'], $searchData['sortOrder']);

		if ($searchData['sortField'] != "id")
			$menus->orderBy('id', 'asc');

		if ($searchData['terms'] != "")
			$menus->where('name', 'like', $searchData['likeTerms']);

		return $menus->paginate($searchData['itemsPerPage']);
	}

}