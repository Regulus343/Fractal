<?php namespace Regulus\Fractal;

/*----------------------------------------------------------------------------------------------------------
	Fractal
		A simple, versatile CMS base for Laravel 4 which uses Twitter Bootstrap.

		created by Cody Jassman
		version 0.5.5a
		last updated on September 1, 2014
----------------------------------------------------------------------------------------------------------*/

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

use Regulus\Fractal\Models\ContentPage;
use Regulus\Fractal\Models\ContentFile;
use Regulus\Fractal\Models\Menu;
use Regulus\Fractal\Models\MenuItem;
use Regulus\Fractal\Models\Setting;

use \Site as Site;
use \Form as Form;
use \Format as Format;
use \HTML as HTML;

use MaxHoffmann\Parsedown\ParsedownFacade as Markdown;

class Fractal {

	/**
	 * The authorization config settings.
	 *
	 * @var    array
	 */
	public $auth;

	/**
	 * The views location for the current controller.
	 *
	 * @var    string
	 */
	public $viewsLocation;

	/**
	 * The pagination data setup.
	 *
	 * @var    array
	 */
	public $pagination;

	/**
	 * The current content type.
	 *
	 * @var    array
	 */
	public $contentType;

	/**
	 * Create a CMS URL.
	 *
	 * @param  string   $uri
	 * @return string
	 */
	public function url($uri = '')
	{
		return URL::to($this->uri($uri));
	}

	/**
	 * Create a CMS URI.
	 *
	 * @param  string   $uri
	 * @return string
	 */
	public function uri($uri = '')
	{
		$fullUri = Config::get('fractal::baseUri');
		if ($fullUri != "" && $fullUri !== false && !is_null($fullUri))
			$fullUri .= '/'.$uri;
		else
			$fullUri = $uri;

		return $fullUri;
	}

	/**
	 * Create a CMS URL for a particular controller.
	 *
	 * @param  string   $uri
	 * @param  string   $controller
	 * @return string
	 */
	public function controllerUrl($uri = '', $controller)
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

		return $this->url($uri);
	}

	/**
	 * Create a blog URL.
	 *
	 * @param  string   $uri
	 * @return string
	 */
	public function blogUrl($uri = '')
	{
		$url = URL::to($this->blogUri($uri));

		$subdomain = Config::get('fractal::blog.subdomain');
		if ($subdomain != "" && $subdomain !== false && !is_null($subdomain)) {
			$url = str_replace($subdomain.'.', '', $url);
			$url = str_replace('http://', 'http://'.$subdomain.'.', str_replace('https://', 'https://'.$subdomain.'.', $url));
		}

		return $url;
	}

	/**
	 * Create a blog URI.
	 *
	 * @param  string   $uri
	 * @return string
	 */
	public function blogUri($uri = '')
	{
		$fullUri = Config::get('fractal::blog.baseUri');
		if ($fullUri != "" && $fullUri !== false && !is_null($fullUri))
			$fullUri .= '/'.$uri;
		else
			$fullUri = $uri;

		return $fullUri;
	}

	/**
	 * Set the views location for a controller.
	 *
	 * @param  mixed    $directory
	 * @return string
	 */
	public function setViewsLocation($directory = null)
	{
		if (is_null($directory))
			$directory = Str::plural($this->getContentType());

		$this->viewsLocation = Config::get('fractal::viewsLocation').$directory.'.';
	}

	/**
	 * Get the views location for a controller or include.
	 *
	 * @param  string   $relativeLocation
	 * @param  boolean  $root
	 * @return string
	 */
	public function view($relativeLocation, $root = false)
	{
		if ($root)
			return Config::get('fractal::viewsLocation').$relativeLocation;
		else
			return $this->viewsLocation.$relativeLocation;
	}

	/**
	 * Get the blog views location for an include.
	 *
	 * @param  string   $relativeLocation
	 * @return string
	 */
	public function blogView($relativeLocation)
	{
		return $this->view('blogs.view.'.$relativeLocation, true);
	}

	/**
	 * Get a response for a modal view request.
	 *
	 * @param  string   $relativeLocation
	 * @param  array    $data
	 * @param  boolean  $root
	 * @param  boolean  $returnJson
	 * @return mixed
	 */
	public function modalView($relativeLocation = '', $data = array(), $root = false, $returnJson = true)
	{
		if (substr($relativeLocation, 0, 7) != "modals.")
			$relativeLocation = "modals.".$relativeLocation;

		$response = array(
			'title'   => isset($data['title']) ? $data['title'] : '',
			'content' => View::make($this->view($relativeLocation, $root))->with($data)->render(),
			'buttons' => isset($data['buttons']) && $data['buttons'] ? true : false,
		);

		if ($returnJson)
			$response = json_encode($response);

		return $response;
	}

	/**
	 * Get the value of a setting by name.
	 *
	 * @param  string   $name
	 * @param  mixed    $default
	 * @return mixed
	 */
	public function getSetting($name, $default = false)
	{
		if (!Config::get('fractal::migrated') || App::runningInConsole())
			return $default;

		return Setting::value($name, $default);
	}

	/**
	 * Export the settings to a PHP array config file.
	 *
	 * @param  mixed    $settings
	 * @param  boolean  $fromCli
	 * @return void
	 */
	public function exportSettings($settings = null, $fromCli = false)
	{
		$array = Setting::createArray($settings);
		$path  = "app/config/packages/regulus/fractal/settings.php";

		if (!$fromCli)
			$path = "../".$path;

		ArrayFile::save($path, $array);
	}

	/**
	 * Set the current content type.
	 *
	 * @param  string   $contentType
	 * @param  boolean  $setViewsLocation
	 */
	public function setContentType($contentType, $setViewsLocation = false)
	{
		$this->contentType = $contentType;

		if ($setViewsLocation)
			$this->setViewsLocation();
	}

	/**
	 * Get the current content type.
	 *
	 * @return string
	 */
	public function getContentType()
	{
		return $this->contentType;
	}

	/**
	 * Get get a content type filter session variable.
	 *
	 * @param  string   $name
	 * @param  mixed    $default
	 * @return string
	 */
	public function getContentTypeFilter($name = '', $default = false)
	{
		return Session::get($name.$this->getContentTypeUpperCase(), $default);
	}

	/**
	 * Turn a dashed content type like "user-roles" into an uppercase words content type like "UserRoles".
	 *
	 * @param  mixed    $contentType
	 * @return string
	 */
	public function getContentTypeUpperCase($contentType = null)
	{
		if (is_null($contentType))
			$contentType = $this->getContentType();

		return $this->toUpperCase($contentType);
	}

	/**
	 * Turn a dashed content type like "user-roles" into a camelcase words content type like "userRoles".
	 *
	 * @param  mixed    $contentType
	 * @return string
	 */
	public function getContentTypeCamelCase($contentType = null)
	{
		if (is_null($contentType))
			$contentType = $this->getContentType();

		return $this->toCamelCase($contentType);
	}

	/**
	 * Turn a dashed or underscored string to uppercase words with no spaces.
	 *
	 * @param  string   $string
	 * @return string
	 */
	public function toUpperCase($string)
	{
		return str_replace(' ', '', ucwords(str_replace('-', ' ', str_replace('_', ' ', strtolower($string)))));
	}

	/**
	 * Turn a dashed or underscored string to camelcase format.
	 *
	 * @param  string   $string
	 * @return string
	 */
	public function toCamelCase($string)
	{
		$string = $this->toUpperCase($string);
		return strtolower(substr($string, 0, 1)).substr($string, 1);
	}

	/**
	 * Setup pagination.
	 *
	 * @param  array    $defaultSorting
	 * @return array
	 */
	public function setupPagination($defaultSorting = array())
	{
		$contentType = $this->getContentType();

		if (empty($defaultSorting))
			$defaultSorting = Site::get('defaultSorting');

		$terms = trim(Input::get('search'));
		$this->pagination = array(
			'itemsPerPage' => $this->getSetting('Items Listed Per Page', 20),
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
		if ($this->pagination['sortField'] == "" && !empty($defaultSorting)) {
			if (isset($defaultSorting['field']))
				$this->pagination['sortField'] = $defaultSorting['field'];

			if (isset($defaultSorting['order']))
				$this->pagination['sortOrder'] = (strtolower($defaultSorting['order']) == 'desc' ? 'desc' : 'asc');
		} 

		//attempt to disallow sorting on fields that "sortable" is not set for via "tables" config
		$sortableFields   = Fractal::getSortableFieldsForTable($contentType);
		if (!in_array($this->pagination['sortField'], $sortableFields))
			$this->pagination['sortField'] = "id";

		$contentTypeUpperCase = $this->getContentTypeUpperCase();

		if ($contentType) {
			if ($this->pagination['search']) {
				Session::set('searchTerms'.$contentTypeUpperCase, $terms);
				Session::set('page'.$contentTypeUpperCase, $this->pagination['page']);

				Session::set('sortField'.$contentTypeUpperCase, $this->pagination['sortField']);
				Session::set('sortOrder'.$contentTypeUpperCase, $this->pagination['sortOrder']);
			} else {
				$this->pagination['terms']     = Session::get('searchTerms'.$contentTypeUpperCase, $terms);
				$this->pagination['page']      = Session::get('page'.$contentTypeUpperCase, $this->pagination['page']);

				$this->pagination['sortField'] = Session::get('sortField'.$contentTypeUpperCase, $this->pagination['sortField']);
				$this->pagination['sortOrder'] = Session::get('sortOrder'.$contentTypeUpperCase, $this->pagination['sortOrder']);
			}
		}

		DB::getPaginator()->setCurrentPage($this->pagination['page']);

		$this->pagination['likeTerms']         = '%'.$this->pagination['terms'].'%';
		$this->pagination['result']['message'] = Lang::get('fractal::messages.searchNoResults', array('terms' => $this->pagination['terms']));

		return $this->pagination;
	}

	/**
	 * Add content for pagination.
	 *
	 * @param  mixed    $content
	 * @return array
	 */
	public function setContentForPagination($content)
	{
		$this->pagination['content'] = $content;
	}

	/**
	 * Set the pagination message and return the pagination data.
	 *
	 * @return array
	 */
	public function setPaginationMessage()
	{
		$contentType = $this->getContentTypeCamelCase();
		$item        = Lang::get('fractal::labels.'.Str::singular($contentType));

		$this->pagination['result']['resultType'] = $this->pagination['content']->getTotal() ? "Success" : "Error";

		if ($this->pagination['changingPage'] || $this->pagination['terms'] == "") {
			$this->pagination['result']['message'] = Lang::get('fractal::messages.displayingItemsOfTotal', array(
				'start'  => $this->pagination['content']->getFrom(),
				'end'    => $this->pagination['content']->getTo(),
				'total'  => $this->pagination['content']->getTotal(),
				'items'  => Format::pluralize(strtolower($item), $this->pagination['content']->getTotal()),
			));
		} else {
			$this->pagination['result']['message'] = Lang::get('fractal::messages.searchResults', array(
				'terms' => $this->pagination['terms'],
				'total' => $this->pagination['content']->getTotal(),
				'items' => Format::pluralize(strtolower($item), $this->pagination['content']->getTotal()),
			));
		}

		return $this->pagination;
	}

	/**
	 * Get the pagination message array.
	 *
	 * @return array
	 */
	public function getPaginationMessageArray()
	{
		if (!isset($this->pagination['result']['message']))
			$this->setPaginationMessage();

		return array(
			strtolower($this->pagination['result']['resultType']) => $this->pagination['result']['message']
		);
	}

	/**
	 * Set the search form default values and return them.
	 *
	 * @return array
	 */
	public function setSearchFormDefaults()
	{
		$defaults = array(
			'search' => $this->pagination['terms']
		);
		Form::setDefaults($defaults);

		return $defaults;
	}

	/**
	 * Get the current content page.
	 *
	 * @return integer
	 */
	public function getCurrentPage()
	{
		if (empty($this->pagination['content']))
			return 1;

		return $this->pagination['content']->getCurrentPage();
	}

	/**
	 * Get the last content page.
	 *
	 * @return integer
	 */
	public function getLastPage()
	{
		if (empty($this->pagination['content']))
			return 1;

		return $this->pagination['content']->getLastPage();
	}

	/**
	 * Get sortable fields for table.
	 *
	 * @param  string   $contentType
	 * @return array
	 */
	public function getSortableFieldsForTable($name = null)
	{
		if (is_null($name))
			$name = Str::plural(Fractal::getContentType());

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
	public function createTable($content = array(), $bodyOnly = false)
	{
		return HTML::table(Config::get('fractal::tables.'.Str::plural($this->getContentTypeCamelCase())), $content, $bodyOnly);
	}

	/**
	 * Render content for a content area.
	 *
	 * @param  string   $content
	 * @param  string   $contentType
	 * @param  boolean  $previewOnly
	 * @return string
	 */
	public function renderContent($content, $contentType = 'HTML', $previewOnly = false)
	{
		//replace HTML special character quotation marks with actual quotation marks
		$content = str_replace('&quot;', '"', $content);

		//add file images to content
		$content = $this->renderContentImages($content, '/\!\[file:([0-9]*)([\;A-Za-z0-9\-\.\#\ ]*)\]/'); //Markdown style image replacement
		$content = $this->renderContentImages($content, '/\[image:([0-9]*)([\;A-Za-z0-9\-\.\#\ ]*)\]/'); //custom "image" replacement tag to match others

		//add file links to content
		preg_match_all('/\[file:([0-9]*)\]/', $content, $fileIds);
		if (isset($fileIds[0]) && !empty($fileIds[0])) {
			$files = ContentFile::whereIn('id', $fileIds[1])->get();

			for ($f = 0; $f < count($fileIds[0]); $f++) {
				$name = "";
				$url  = "";
				foreach ($files as $file) {
					if ((int) $file->id == (int) $fileIds[1][$f]) {
						$name = $file->name;
						$url  = $file->getUrl();
					}
				}

				if ($url != "") {
					if (substr($url, 0, strlen(Config::get('app.url'))) == Config::get('app.url'))
						$link = '<a href="'.$url.'">'.$name.'</a>';
					else
						$link = '<a href="'.$url.'" target="_blank">'.$name.'</a>';

					$content = str_replace($fileIds[0][$f], $link, $content);
				}
			}
		}

		//add file URLs to content
		preg_match_all('/file:([0-9]*)/', $content, $fileIds);
		if (isset($fileIds[0]) && !empty($fileIds[0])) {
			$files = ContentFile::whereIn('id', $fileIds[1])->get();

			for ($f = 0; $f < count($fileIds[0]); $f++) {
				$url = "";
				foreach ($files as $file) {
					if ((int) $file->id == (int) $fileIds[1][$f])
						$url = $file->getUrl();
				}

				if ($url != "")
					$content = str_replace($fileIds[0][$f], $url, $content);
			}
		}

		//add page links to content
		preg_match_all('/\[page:([a-z\-]*)\]/', $content, $pageSlugs);
		if (isset($pageSlugs[0]) && !empty($pageSlugs[0])) {
			$pages = ContentPage::whereIn('slug', $pageSlugs[1])->get();

			for ($f = 0; $f < count($pageSlugs[0]); $f++) {
				$title = "";
				$url   = "";
				foreach ($pages as $page) {
					if ($page->slug == $pageSlugs[1][$f]) {
						$title = $page->title;
						$url   = $page->getUrl();
					}
				}

				if ($url != "") {
					$link    = '<a href="'.$url.'">'.$title.'</a>';
					$content = str_replace($pageSlugs[0][$f], $link, $content);
				}
			}
		}

		//add page links to content
		preg_match_all('/page:([a-z\-]*)/', $content, $pageSlugs);
		if (isset($pageSlugs[0]) && !empty($pageSlugs[0])) {
			$pages = ContentPage::whereIn('slug', $pageSlugs[1])->get();

			for ($f = 0; $f < count($pageSlugs[0]); $f++) {
				$url = "";
				foreach ($pages as $page) {
					if ($page->slug == $pageSlugs[1][$f])
						$url = $page->getUrl();
				}

				if ($url != "")
					$content = str_replace($pageSlugs[0][$f], $url, $content);
			}
		}

		//render views in content
		preg_match_all('/\[view:\"([a-z\:\.\_\-]*)\"\]/', $content, $views);
		if (isset($views[0]) && !empty($views[0])) {
			for ($v = 0; $v < count($views[0]); $v++) {
				$view    = View::make($views[1][$v])->render();
				$content = str_replace($views[0][$v], $view, $content);
			}
		}

		//embed YouTube videos in content
		preg_match_all('/\[youtube:([A-Za-z0-9\_\-]{11})\]/', $content, $videos);
		if (isset($videos[0]) && !empty($videos[0])) {
			for ($v = 0; $v < count($videos[0]); $v++) {
				$video   = '<iframe width="420" height="315" src="http://www.youtube.com/embed/'.$videos[1][$v].'" frameborder="0" allowfullscreen></iframe>';
				$content = str_replace($videos[0][$v], $video, $content);
			}
		}

		//render to Markdown
		if (strtolower($contentType) == "markdown")
			$content = Markdown::parse($content);

		//cut off content after the preview divider for blog articles if preview only option is set
		$previewDivider = Config::get('fractal::blog.previewDivider');
		if ($previewOnly) {
			$dividerPosition = strpos($content, $previewDivider);
			if ($dividerPosition)
				$content = substr($content, 0, $dividerPosition);
		} else {
			$content = str_replace($previewDivider, '', str_replace("\n".$previewDivider."\n", '', $content));
		}

		//convert lone ampersands to HTML special characters
		$content = str_replace(' & ', ' &amp; ', $content);

		return $content;
	}

	/**
	 * Render Markdown content for a content area.
	 *
	 * @param  string   $content
	 * @return string
	 */
	public function renderMarkdownContent($content)
	{
		return $this->renderContent($content, 'Markdown');
	}

	/**
	 * Render Markdown content for a content area.
	 *
	 * @param  string   $content
	 * @param  string   $expression
	 * @return string
	 */
	public function renderContentImages($content, $expression)
	{
		preg_match_all($expression, $content, $fileIds);
		if (isset($fileIds[0]) && !empty($fileIds[0])) {
			$files = ContentFile::whereIn('id', $fileIds[1])->get();

			for ($f = 0; $f < count($fileIds[0]); $f++) {
				$name = "";
				$url  = "";
				foreach ($files as $file) {
					if ((int) $file->id == (int) $fileIds[1][$f]) {
						$name = $file->name;
						$url  = $file->getUrl();
					}
				}

				$id      = "";
				$classes = [];
				if (isset($fileIds[2][$f]) && !empty($fileIds[2][$f])) {
					$idClasses = str_replace(';', '', explode(' ', $fileIds[2][$f]));

					foreach ($idClasses as $idClass) {
						if (substr($idClass, 0, 1) == "#") {
							$id = str_replace('#', '', $idClass);
						} else if (substr($idClass, 0, 1) == ".") {
							$classes[] = str_replace('.', '', $idClass);
						}
					}
				}

				if ($url != "") {
					$image = '<img src="'.$url.'" alt="'.$name.'" title="'.$name.'" ';

					if ($id != "")
						$image .= 'id="'.$id.'" ';

					if (!empty($classes))
						$image .= 'class="'.implode(' ', $classes).'" ';

					$image .= '/>';

					$content = str_replace($fileIds[0][$f], $image, $content);
				}
			}
		}

		return $content;
	}

	/**
	 * Authenticates users for the default CMS views while remaining authorization class-agnostic.
	 *
	 * @return boolean
	 */
	public function auth()
	{
		$auth = $this->configAuth();
		if ($auth->methodActiveCheck != false) {
			$function = $this->separateFunction($auth->methodActiveCheck);
			return $this->callFunction($function);
		}
		return false;
	}

	/**
	 * Authenticates admin for the default CMS views while remaining authorization class-agnostic.
	 *
	 * @return boolean
	 */
	public function admin()
	{
		$auth = $this->configAuth();
		if ($auth->methodAdminCheck) {
			if ($this->auth()) {
				$user = $this->user();
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
	public function roles($roles)
	{
		$auth = $this->configAuth();

		$allowed = false;
		if ($this->auth()) {
			$userRoles = $this->user()->roles;
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
	public function user()
	{
		$auth = $this->configAuth();
		if ($auth->methodActiveUser != false) {
			$function = $this->separateFunction($auth->methodActiveUser);
			return $this->callFunction($function);
		}
		return false;
	}

	/**
	 * Gets the active user ID.
	 *
	 * @return boolean
	 */
	public function userID()
	{
		$auth = $this->configAuth();
		$user = $this->user();

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
	public function userByUsername($username)
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
	public function getRegionLabel($country)
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
	private function configAuth()
	{
		if (is_null($this->auth)) {
			$this->auth = (object) array(
				'class'              => Config::get('fractal::authClass'),
				'methodActiveCheck'  => Config::get('fractal::authMethodActiveCheck'),
				'methodActiveUser'   => Config::get('fractal::authMethodActiveUser'),
				'methodActiveUserID' => Config::get('fractal::authMethodActiveUserID'),
				'methodAdminCheck'   => Config::get('fractal::authMethodAdminCheck'),
				'methodAdminRole'    => Config::get('fractal::authMethodAdminRole'),
			);
		}
		return $this->auth;
	}

	/**
	 * Separates a function string "function('array')" into the
	 * function name and the parameters for use with call_user_func.
	 *
	 * @param  string   $function
	 * @return object
	 */
	public function separateFunction($function)
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
	public function callFunction($function)
	{
		if (!isset($function->method) OR !isset($function->parameters)) return false;

		$auth = $this->configAuth();
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
	 * Export the menus to a PHP array config file.
	 *
	 * @param  boolean  $fromCli
	 * @return void
	 */
	public function exportMenus($fromCli = false)
	{
		$menus = Menu::orderBy('cms', 'desc')->orderBy('name')->get();
		$array = array();
		foreach ($menus as $menu) {
			$array[$this->toCamelCase($menu->name)] = $menu->createArray(false, true);
		}

		$path  = "app/config/packages/regulus/fractal/menus.php";

		if (!$fromCli)
			$path = "../".$path;

		ArrayFile::save($path, $array);
	}

	/**
	 * Get a menu array.
	 *
	 * @return array
	 */
	public function getMenuArray($name = 'Main')
	{
		return Menu::getArray($name);
	}

	/**
	 * Get menu markup for Bootstrap.
	 *
	 * @param  string   $name
	 * @param  boolean  $listItemsOnly
	 * @param  string   $class
	 * @return string
	 */
	public function getMenuMarkup($name = 'Main', $listItemsOnly = false, $class = '')
	{
		return Menu::createMarkupForMenu($name, $listItemsOnly, $class);
	}

	/**
	 * Get menu item selected class.
	 *
	 * @param  array    $name
	 * @return string
	 */
	public function setMenuItemSelectedClass($menuItem)
	{
		return MenuItem::setSelectedClass($menuItem);
	}

	/**
	 * Get the visibility status of a menu item.
	 *
	 * @param  object   $menuItem
	 * @return boolean
	 */
	public function isMenuItemVisible($menuItem)
	{
		$visible = true;

		if (! (bool) $menuItem->active)
			$visible = false;

		if ($menuItem->type == "Content Page" && (is_null($menuItem->pagePublishedDate) || strtotime($menuItem->pagePublishedDate) > time()))
			$visible = false;

		if ($menuItem->authStatus) {
			if (Fractal::auth() && (int) $menuItem->authStatus == 2)
				$visible = false;

			if (!Fractal::auth() && (int) $menuItem->authStatus == 1)
				$visible = false;
		}

		return $visible;
	}

	/**
	 * Get an array of layout tags from a layout string.
	 *
	 * @param  string   $layout
	 * @return array
	 */
	public function getLayoutTagsFromLayout($layout = '')
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
	public function getDateFormat()
	{
		return Config::get('fractal::dateFormat');
	}

	/**
	 * Get the date-time format.
	 *
	 * @return string
	 */
	public function getDateTimeFormat()
	{
		return Config::get('fractal::dateTimeFormat');
	}

	/**
	 * Check whether a date is set.
	 *
	 * @param  string   $date
	 * @return boolean
	 */
	public function dateSet($date)
	{
		return $date != "0000-00-00";
	}

	/**
	 * Check whether a date-time is set.
	 *
	 * @param  string   $dateTime
	 * @return boolean
	 */
	public function dateTimeSet($dateTime)
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
	public function datePast($date, $dateToCompare = null, $includeEqual = false)
	{
		if (is_null($dateToCompare) || $dateToCompare === false)
			$dateToCompare = date('Y-m-d');

		if (!$this->dateSet($date))
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
	public function dateTimePast($dateTime, $dateTimeToCompare = null, $includeEqual = false)
	{
		if (is_null($dateTimeToCompare) || $dateTimeToCompare === false)
			$dateTimeToCompare = date('Y-m-d H:i:s');

		if (!$this->dateTimeSet($dateTime))
			return false;

		if ($includeEqual)
			return strtotime($dateTime) <= strtotime($dateTimeToCompare);
		else
			return strtotime($dateTime) < strtotime($dateTimeToCompare);
	}

}