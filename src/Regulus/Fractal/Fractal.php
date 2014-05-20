<?php namespace Regulus\Fractal;

/*----------------------------------------------------------------------------------------------------------
	Fractal
		A simple, versatile CMS base for Laravel 4 which uses Twitter Bootstrap.

		created by Cody Jassman
		version 0.33
		last updated on May 19, 2014
----------------------------------------------------------------------------------------------------------*/

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;

use Aquanode\Elemental\Elemental as HTML;
use Aquanode\Formation\Formation as Form;
use Regulus\SolidSite\SolidSite as Site;
use Regulus\TetraText\TetraText as Format;

class Fractal {

	/**
	 * The authorization config settings.
	 *
	 * @var    array
	 */
	public static $auth;

	/**
	 * The views location for the current controller.
	 *
	 * @var    string
	 */
	public static $viewsLocation;

	/**
	 * The pagination data setup.
	 *
	 * @var    array
	 */
	public static $pagination;

	/**
	 * The current content type.
	 *
	 * @var    array
	 */
	public static $contentType;

	/**
	 * Create a CMS URL.
	 *
	 * @param  string   $uri
	 * @return string
	 */
	public static function url($uri = '')
	{
		return URL::to(static::uri($uri));
	}

	/**
	 * Create a CMS URI.
	 *
	 * @param  string   $uri
	 * @return string
	 */
	public static function uri($uri = '')
	{
		$fullUri = Config::get('fractal::baseUri');
		if ($fullUri != "") $fullUri .= '/'.$uri;
		return $fullUri;
	}

	/**
	 * Create a CMS URL for a particular controller.
	 *
	 * @param  string   $uri
	 * @param  string   $controller
	 * @return string
	 */
	public static function controllerUrl($uri = '', $controller)
	{
		$controller   = ucfirst($controller).'Controller';
		$controllers  = Config::get('fractal::controllers');
		$uriCompleted = false;
		if (isset($controllers['standard'])) {
			foreach ($controllers['standard'] as $controllerUri => $controllerListed) {
				if (!$uriCompleted) {
					$controllerArray = explode('\\', $controllerListed);
					$controllerName  = end($controllerArray);
					if ($controllerName == $controller) {
						$uri = $controllerUri.'/'.$uri;
						$uriCompleted = true;
					}
				}
			}
		}
		if (isset($controllers['resource'])) {
			foreach ($controllers['resource'] as $controllerUri => $controllerListed) {
				if (!$uriCompleted) {
					$controllerArray = explode('\\', $controllerListed);
					$controllerName  = end($controllerArray);
					if ($controllerName == $controller) {
						$uri = $controllerUri.'/'.$uri;
						$uriCompleted = true;
					}
				}
			}
		}

		return static::url($uri);
	}

	/**
	 * Set the views location for a controller.
	 *
	 * @param  mixed    $directory
	 * @return string
	 */
	public static function setViewsLocation($directory = null)
	{
		if (is_null($directory))
			$directory = static::getContentType();

		static::$viewsLocation = Config::get('fractal::viewsLocation').$directory.'.';
	}

	/**
	 * Get the views location for a controller.
	 *
	 * @param  string   $relativeLocation
	 * @param  boolean  $root
	 * @return string
	 */
	public static function view($relativeLocation = '', $root = false)
	{
		if ($root)
			return Config::get('fractal::viewsLocation').$relativeLocation;
		else
			return static::$viewsLocation.$relativeLocation;
	}

	/**
	 * Get the value of a setting by name.
	 *
	 * @param  string   $name
	 * @param  mixed    $default
	 * @return mixed
	 */
	public static function getSetting($name, $default = false)
	{
		if (!Config::get('fractal::migrated') || App::runningInConsole())
			return $default;

		return Setting::value($name, $default);
	}

	/**
	 * Set the current content type.
	 *
	 * @param  string   $contentType
	 * @param  boolean  $setViewsLocation
	 */
	public static function setContentType($contentType, $setViewsLocation = false)
	{
		static::$contentType = $contentType;

		if ($setViewsLocation)
			static::setViewsLocation();
	}

	/**
	 * Get the current content type.
	 *
	 * @return string
	 */
	public static function getContentType()
	{
		return static::$contentType;
	}

	/**
	 * Get get a content type filter session variable.
	 *
	 * @param  string   $name
	 * @param  mixed    $default
	 * @return string
	 */
	public static function getContentTypeFilter($name = '', $default = false)
	{
		return Session::get($name.static::getContentTypeUpperCase(), $default);
	}

	/**
	 * Turn a dashed content type like "user-roles" into an uppercase words content type like "UserRoles".
	 *
	 * @param  mixed    $contentType
	 * @return string
	 */
	public static function getContentTypeUpperCase($contentType = null)
	{
		if (is_null($contentType))
			$contentType = static::getContentType();

		return str_replace(' ', '', ucwords(str_replace('-', ' ', $contentType)));
	}

	/**
	 * Turn a dashed content type like "user-roles" into a camelcase words content type like "userRoles".
	 *
	 * @param  mixed    $contentType
	 * @return string
	 */
	public static function getContentTypeCamelCase($contentType = null)
	{
		$contentType = static::getContentTypeUpperCase($contentType);
		return strtolower(substr($contentType, 0, 1)).substr($contentType, 1);
	}

	/**
	 * Setup pagination.
	 *
	 * @param  array    $defaultSorting
	 * @return array
	 */
	public static function setupPagination($defaultSorting = array())
	{
		$contentType = static::getContentType();

		if (empty($defaultSorting))
			$defaultSorting = Site::get('defaultSorting');

		$terms = trim(Input::get('search'));
		static::$pagination = array(
			'itemsPerPage' => static::getSetting('Items Listed Per Page', 20),
			'terms'        => $terms,
			'likeTerms'    => '%'.$terms.'%',
			'search'       => $_POST ? true : false,
			'page'         => !is_null(Input::get('page')) ? Input::get('page') : 1,
			'changingPage' => (bool) Input::get('changing_page'),
			'sortField'    => Input::get('sort_field'),
			'sortOrder'    => (strtolower(Input::get('sort_order')) == 'desc' ? 'desc' : 'asc'),
			'contentType'  => $contentType,
			'result'       => array(
				'resultType' => 'Error',
			),
		);

		//set default sorting
		if (static::$pagination['sortField'] == "" && !empty($defaultSorting)) {
			if (isset($defaultSorting['field']))
				static::$pagination['sortField'] = $defaultSorting['field'];

			if (isset($defaultSorting['order']))
				static::$pagination['sortOrder'] = (strtolower($defaultSorting['order']) == 'desc' ? 'desc' : 'asc');
		} 

		//attempt to disallow sorting on fields that "sortable" is not set for via "tables" config
		$sortableFields   = Fractal::getSortableFieldsForTable($contentType);
		if (!in_array(static::$pagination['sortField'], $sortableFields))
			static::$pagination['sortField'] = "id";

		$contentTypeUpperCase = static::getContentTypeUpperCase();

		if ($contentType) {
			if (static::$pagination['search']) {
				Session::set('searchTerms'.$contentTypeUpperCase, $terms);
				Session::set('page'.$contentTypeUpperCase, static::$pagination['page']);

				Session::set('sortField'.$contentTypeUpperCase, static::$pagination['sortField']);
				Session::set('sortOrder'.$contentTypeUpperCase, static::$pagination['sortOrder']);
			} else {
				static::$pagination['terms']     = Session::get('searchTerms'.$contentTypeUpperCase, $terms);
				static::$pagination['page']      = Session::get('page'.$contentTypeUpperCase, static::$pagination['page']);

				static::$pagination['sortField'] = Session::get('sortField'.$contentTypeUpperCase, static::$pagination['sortField']);
				static::$pagination['sortOrder'] = Session::get('sortOrder'.$contentTypeUpperCase, static::$pagination['sortOrder']);
			}
		}

		DB::getPaginator()->setCurrentPage(static::$pagination['page']);

		static::$pagination['likeTerms']         = '%'.static::$pagination['terms'].'%';
		static::$pagination['result']['message'] = Lang::get('fractal::messages.searchNoResults', array('terms' => static::$pagination['terms']));

		return static::$pagination;
	}

	/**
	 * Add content for pagination.
	 *
	 * @param  mixed    $content
	 * @return array
	 */
	public static function setContentForPagination($content)
	{
		static::$pagination['content'] = $content;
	}

	/**
	 * Set the pagination message and return the pagination data.
	 *
	 * @return array
	 */
	public static function setPaginationMessage()
	{
		$contentType = static::getContentTypeCamelCase();
		$item        = Lang::get('fractal::labels.'.Str::singular($contentType));

		static::$pagination['result']['resultType'] = static::$pagination['content']->getTotal() ? "Success" : "Error";

		if (static::$pagination['changingPage'] || static::$pagination['terms'] == "") {
			static::$pagination['result']['message'] = Lang::get('fractal::messages.displayingItemsOfTotal', array(
				'start'  => static::$pagination['content']->getFrom(),
				'end'    => static::$pagination['content']->getTo(),
				'total'  => static::$pagination['content']->getTotal(),
				'items'  => Format::pluralize(strtolower($item), static::$pagination['content']->getTotal()),
			));
		} else {
			static::$pagination['result']['message'] = Lang::get('fractal::messages.searchResults', array(
				'terms' => static::$pagination['terms'],
				'total' => static::$pagination['content']->count(),
				'items' => Format::pluralize(strtolower($item), static::$pagination['content']->count()),
			));
		}

		return static::$pagination;
	}

	/**
	 * Get the pagination message array.
	 *
	 * @return array
	 */
	public static function getPaginationMessageArray()
	{
		if (!isset(static::$pagination['result']['message']))
			static::setPaginationMessage();

		return array(
			strtolower(static::$pagination['result']['resultType']) => static::$pagination['result']['message']
		);
	}

	/**
	 * Set the search form default values and return them.
	 *
	 * @return array
	 */
	public static function setSearchFormDefaults()
	{
		$defaults = array(
			'search' => static::$pagination['terms']
		);
		Form::setDefaults($defaults);

		return $defaults;
	}

	/**
	 * Get the current content page.
	 *
	 * @return integer
	 */
	public static function getCurrentPage()
	{
		if (empty(static::$pagination['content']))
			return 1;

		return static::$pagination['content']->getCurrentPage();
	}

	/**
	 * Get the last content page.
	 *
	 * @return integer
	 */
	public static function getLastPage()
	{
		if (empty(static::$pagination['content']))
			return 1;

		return static::$pagination['content']->getLastPage();
	}

	/**
	 * Get sortable fields for table.
	 *
	 * @param  string   $contentType
	 * @return array
	 */
	public static function getSortableFieldsForTable($name = null)
	{
		if (is_null($name))
			$name = Fractal::getContentType();

		$fields      = array();
		$tableConfig = Config::get('fractal::tables.'.$name);

		if (empty($tableConfig) || !isset($tableConfig['columns']))
			return $fields;

		foreach ($tableConfig['columns'] as $column) {
			if (isset($column['sort'])) {
				if (is_bool($column['sort']) && $column['sort'] && isset($column['attribute']))
					$field = $column['attribute'];
				else
					$field = $column['sort'];

				$fields[] = $field;
			}
		}

		return $fields;
	}

	/**
	 * Create a table from a table config array.
	 *
	 * @param  mixed    $content
	 * @param  boolean  $bodyOnly
	 * @return string
	 */
	public static function createTable($content = array(), $bodyOnly = false)
	{
		return HTML::table(Config::get('fractal::tables.'.static::getContentTypeCamelCase()), $content, $bodyOnly);
	}

	/**
	 * Authenticates users for the default CMS views while remaining authorization class-agnostic.
	 *
	 * @return boolean
	 */
	public static function auth()
	{
		$auth = static::configAuth();
		if ($auth->methodActiveCheck != false) {
			$function = static::separateFunction($auth->methodActiveCheck);
			return static::callFunction($function);
		}
		return false;
	}

	/**
	 * Authenticates admin for the default CMS views while remaining authorization class-agnostic.
	 *
	 * @return boolean
	 */
	public static function admin()
	{
		$auth = static::configAuth();
		if ($auth->methodAdminCheck) {
			if (static::auth()) {
				$user = static::user();
				if ($user->roles[0]->role == $auth->methodAdminRole) return true;
			}
		}
		return false;
	}

	/**
	 * Checks whether active user has one of the given roles. You may provide an array or a string with the name of the role to be checked against.
	 *
	 * @param  mixed    $roles
	 * @return boolean
	 */
	public static function roles($roles)
	{
		$auth = static::configAuth();

		$allowed = false;
		if (static::auth()) {
			$userRoles = static::user()->roles;
			if (is_array($roles)) {
				foreach ($userRoles as $userRole) {
					foreach ($roles as $role) {
						if (strtolower($userRole->role) == strtolower($role))
							$allowed = true;
					}
				}
			} else {
				$role = $roles;
				foreach ($userRoles as $userRole) {
					if (strtolower($userRole->role) == strtolower($role))
						$allowed = true;
				}
			}
		}
		return $allowed;
	}

	/**
	 * Gets the active user.
	 *
	 * @return boolean
	 */
	public static function user()
	{
		$auth = static::configAuth();
		if ($auth->methodActiveUser != false) {
			$function = static::separateFunction($auth->methodActiveUser);
			return static::callFunction($function);
		}
		return false;
	}

	/**
	 * Gets the active user ID.
	 *
	 * @return boolean
	 */
	public static function userID()
	{
		$auth = static::configAuth();
		$user = static::user();

		if (isset($user->{$auth->methodActiveUserID}))
			return $user->{$auth->methodActiveUserID};

		return false;
	}

	/**
	 * Gets a user by their username.
	 *
	 * @param  string   $username
	 * @return User
	 */
	public static function userByUsername($username)
	{
		$users = call_user_func_array("\\".Config::get('auth.model')."::where", array('username', '=', str_replace("'", '', $username)));
		return $users->first();
	}

	/**
	 * Gets the label for the region field based on the selected country.
	 *
	 * @param  string   $country
	 * @return string
	 */
	public static function regionLabel($country)
	{
		switch ($country) {
			case "Canada":        return Lang::get('fractal::labels.province'); break;
			case "United States": return Lang::get('fractal::labels.state');    break;
		}
		return Lang::get('fractal::labels.region'); break;
	}

	/**
	 * Prepare authorization configuration.
	 *
	 * @return array
	 */
	private static function configAuth()
	{
		if (is_null(static::$auth)) {
			static::$auth = (object) array(
				'class'              => Config::get('fractal::authClass'),
				'methodActiveCheck'  => Config::get('fractal::authMethodActiveCheck'),
				'methodActiveUser'   => Config::get('fractal::authMethodActiveUser'),
				'methodActiveUserID' => Config::get('fractal::authMethodActiveUserID'),
				'methodAdminCheck'   => Config::get('fractal::authMethodAdminCheck'),
				'methodAdminRole'    => Config::get('fractal::authMethodAdminRole'),
			);
		}
		return static::$auth;
	}

	/**
	 * Separates a function string "function('array')" into the
	 * function name and the parameters for use with call_user_func.
	 *
	 * @param  string   $function
	 * @return object
	 */
	public static function separateFunction($function)
	{
		$data = preg_match('/([\w\_\d]+)\(([\w\W]*)\)/', $function, $matches);
		if (!isset($matches[0])) $matches[0] = $function;
		if (!isset($matches[1])) $matches[1] = str_replace('()', '', $function);
		if (!isset($matches[2])) $matches[2] = null;
		return (object) array(
			'method'     => $matches[1],
			'parameters' => str_replace("'", '', $matches[2]),
		);
	}

	/**
	 * Calls a function using call_user_func and call_user_func array.
	 *
	 * @param  object   $function
	 * @return boolean
	 */
	public static function callFunction($function)
	{
		if (!isset($function->method) OR !isset($function->parameters)) return false;

		$auth = static::configAuth();
		if (substr($function->parameters, 0, 6) == "array(") {

			$function->parameters = explode(',', $function->parameters);
			for ($p = 0; $p < count($function->parameters); $p++) {
				$function->parameters[$p] = str_replace("'", '', $function->parameters[$p]);
				$function->parameters[$p] = str_replace('array(', '', $function->parameters[$p]);
				$function->parameters[$p] = str_replace(')', '', $function->parameters[$p]);
			}

			return call_user_func_array($auth->class.'::'.$function->method, $function->parameters);
		} else {
			return call_user_func($auth->class.'::'.$function->method, $function->parameters);
		}
	}

	/**
	 * Get a menu array.
	 *
	 * @return array
	 */
	public static function getMenuArray($name = 'Main')
	{
		$menu = Menu::where('name', '=', $name)->first();
		return $menu->createArray();
	}

	/**
	 * Get menu markup for Bootstrap.
	 *
	 * @param  string   $name
	 * @param  boolean  $listItemsOnly
	 * @param  string   $class
	 * @return string
	 */
	public static function getMenuMarkup($name = 'Main', $listItemsOnly = false, $class = '')
	{
		$menu = Menu::where('name', '=', $name)->first();
		return $menu->createMarkup($listItemsOnly, $class);
	}

	/**
	 * Get an array of layout tags from a layout string.
	 *
	 * @param  string   $layout
	 * @return array
	 */
	public static function getLayoutTagsFromLayout($layout = '')
	{
		preg_match_all('/\{\{([a-z0-9\-]*)\}\}/', $layout, $layoutTags);
		if (isset($layoutTags[1]) && is_array($layoutTags[1]))
			return $layoutTags[1];

		return array();
	}

	/**
	 * Get the date format.
	 *
	 * @return string
	 */
	public static function getDateFormat()
	{
		return Config::get('fractal::dateFormat');
	}

	/**
	 * Get the date-time format.
	 *
	 * @return string
	 */
	public static function getDateTimeFormat()
	{
		return Config::get('fractal::dateTimeFormat');
	}

	/**
	 * Check whether a date is set.
	 *
	 * @param  string   $date
	 * @return boolean
	 */
	public static function dateSet($date)
	{
		return $date != "0000-00-00";
	}

	/**
	 * Check whether a date-time is set.
	 *
	 * @param  string   $dateTime
	 * @return boolean
	 */
	public static function dateTimeSet($dateTime)
	{
		return $dateTime != "0000-00-00 00:00:00";
	}

	/**
	 * Check whether a date is past.
	 *
	 * @param  string   $date
	 * @param  mixed    $dateToCompare
	 * @param  boolean  $includeEqual
	 * @return boolean
	 */
	public static function datePast($date, $dateToCompare = null, $includeEqual = false)
	{
		if (is_null($dateToCompare) || $dateToCompare === false)
			$dateToCompare = date('Y-m-d');

		if (!static::dateSet($date))
			return false;

		if ($includeEqual)
			return strtotime($date) <= strtotime($dateToCompare);
		else
			return strtotime($date) < strtotime($dateToCompare);
	}

	/**
	 * Check whether a date-time is past.
	 *
	 * @param  string   $dateTime
	 * @param  mixed    $dateTimeToCompare
	 * @param  boolean  $includeEqual
	 * @return boolean
	 */
	public static function dateTimePast($dateTime, $dateTimeToCompare = null, $includeEqual = false)
	{
		if (is_null($dateTimeToCompare) || $dateTimeToCompare === false)
			$dateTimeToCompare = date('Y-m-d H:i:s');

		if (!static::dateTimeSet($dateTime))
			return false;

		if ($includeEqual)
			return strtotime($dateTime) <= strtotime($dateTimeToCompare);
		else
			return strtotime($dateTime) < strtotime($dateTimeToCompare);
	}

}