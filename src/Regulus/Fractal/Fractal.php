<?php namespace Regulus\Fractal;

/*----------------------------------------------------------------------------------------------------------
	Fractal
		A simple, versatile CMS base for Laravel 4 which uses Twitter Bootstrap.

		created by Cody Jassman
		version 0.23
		last updated on January 3, 2014
----------------------------------------------------------------------------------------------------------*/

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;

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
			foreach ($controllers['standard'] as $controllerURI => $controllerListed) {
				if (!$uriCompleted) {
					$controllerArray = explode('\\', $controllerListed);
					$controllerName  = end($controllerArray);
					if ($controllerName == $controller) {
						$uri = $controllerURI.'/'.$uri;
						$uriCompleted = true;
					}
				}
			}
		}
		if (isset($controllers['resource'])) {
			foreach ($controllers['resource'] as $controllerURI => $controllerListed) {
				if (!$uriCompleted) {
					$controllerArray = explode('\\', $controllerListed);
					$controllerName  = end($controllerArray);
					if ($controllerName == $controller) {
						$uri = $controllerURI.'/'.$uri;
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
	 * @param  string   $directory
	 * @return string
	 */
	public static function setViewsLocation($directory = '')
	{
		static::$viewsLocation = Config::get('fractal::viewsLocation').$directory.'.';
	}

	/**
	 * Get the views location for a controller.
	 *
	 * @param  string   $relativeLocation
	 * @return string
	 */
	public static function view($relativeLocation = '')
	{
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
		return Setting::value($name, $default);
	}

	/**
	 * Setup pagination.
	 *
	 * @param  mixed    $contentType
	 * @return array
	 */
	public static function setupPagination($contentType = null)
	{
		$terms = trim(Input::get('search'));
		static::$pagination = array(
			'itemsPerPage' => static::getSetting('Items Listed Per Page', 20),
			'terms'        => $terms,
			'likeTerms'    => '%'.$terms.'%',
			'search'       => $_POST ? true : false,
			'page'         => !is_null(Input::get('page')) ? Input::get('page') : 1,
			'changingPage' => (bool) Input::get('changing_page'),
			'sortField'    => Input::get('sort_field'),
			'sortOrder'    => (Input::get('sort_order') == 'desc' ? Input::get('sort_order') : 'asc'),
			'contentType'  => $contentType,
			'result'       => array(
				'resultType' => 'Error',
			),
		);

		$contentTypeTable = strtolower(substr($contentType, 0, 1)).substr($contentType, 1);
		$sortableFields   = Fractal::getSortableFieldsForTable($contentTypeTable);
		if (!in_array(static::$pagination['sortField'], $sortableFields))
			static::$pagination['sortField'] = "id";

		if ($contentType) {
			if (static::$pagination['search']) {
				Session::set('searchTerms'.$contentType, $terms);
				Session::set('page'.$contentType, static::$pagination['page']);

				Session::set('sortField'.$contentType, static::$pagination['sortField']);
				Session::set('sortOrder'.$contentType, static::$pagination['sortOrder']);
			} else {
				static::$pagination['terms']     = Session::get('searchTerms'.$contentType, $terms);
				static::$pagination['page']      = Session::get('page'.$contentType, static::$pagination['page']);

				static::$pagination['sortField'] = Session::get('sortField'.$contentType, static::$pagination['sortField']);
				static::$pagination['sortOrder'] = Session::get('sortOrder'.$contentType, static::$pagination['sortOrder']);
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
	 * @param  mixed    $contentType
	 * @return array
	 */
	public static function addContentForPagination($content)
	{
		static::$pagination['content'] = $content;
	}

	public static function setPaginationMessage()
	{
		$item = Lang::get('fractal::labels.'.strtolower(Str::singular(static::$pagination['contentType'])));

		if (static::$pagination['changingPage'] || static::$pagination['terms'] == "") {
			static::$pagination['result']['resultType'] = "Success";
			static::$pagination['result']['message'] = Lang::get('fractal::messages.displayingItemsOfTotal', array(
				'start'  => static::$pagination['content']->getFrom(),
				'end'    => static::$pagination['content']->getTo(),
				'total'  => static::$pagination['content']->getTotal(),
				'items'  => Format::pluralize(strtolower($item), static::$pagination['content']->getTotal()),
			));
		} else {
			static::$pagination['result']['resultType'] = "Success";
			static::$pagination['result']['message'] = Lang::get('fractal::messages.searchResults', array(
				'terms' => static::$pagination['terms'],
				'total' => static::$pagination['content']->count(),
				'items' => Format::pluralize(strtolower($item), static::$pagination['content']->count()),
			));
		}

		return static::$pagination;
	}

	/**
	 * Get sortable fields for table.
	 *
	 * @param  string   $name
	 * @return array
	 */
	public static function getSortableFieldsForTable($name)
	{
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
		//var_dump("\\".Config::get('auth.model')."::where('username', '=', '".str_replace("'", '', $username)."')->first();"); exit;
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

}