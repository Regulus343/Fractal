<?php namespace Regulus\Fractal;

/*----------------------------------------------------------------------------------------------------------
	Fractal
		A versatile CMS for Laravel 5.

		created by Cody Jassman
		version 0.9.5
		last updated on October 28, 2015
----------------------------------------------------------------------------------------------------------*/

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

use Illuminate\Pagination\Paginator;

use Regulus\Fractal\Libraries\ArrayFile;

use Regulus\Fractal\Models\Content\Page;
use Regulus\Fractal\Models\Content\File as ContentFile;
use Regulus\Fractal\Models\Content\Menu;
use Regulus\Fractal\Models\Content\MenuItem;
use Regulus\Fractal\Models\General\Setting;
use Regulus\Fractal\Models\Media\Item as MediaItem;
use Regulus\Fractal\Models\Blog\Article as BlogArticle;

use Auth;
use Form;
use Format;
use HTML;
use Site;

use AlfredoRamos\ParsedownExtra\Facades\ParsedownExtra as Markdown;

class Fractal {

	/**
	 * The authorization config settings.
	 *
	 * @var    array
	 */
	public $auth;

	/**
	 * The current controller route path.
	 *
	 * @var    mixed
	 */
	public $controllerPath;

	/**
	 * The views location for the current controller.
	 *
	 * @var    mixed
	 */
	public $viewsLocation;

	/**
	 * The pagination data setup.
	 *
	 * @var    array
	 */
	public $pagination = [];

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
	 * @param  boolean  $controller
	 * @param  mixed    $subdomain
	 * @return string
	 */
	public function url($uri = '', $controller = false, $subdomain = null)
	{
		if (is_null($subdomain))
			$subdomain = config('cms.subdomain');

		return Site::url($this->uri($uri, $controller), $subdomain);
	}

	/**
	 * Create a CMS URI.
	 *
	 * @param  string   $uri
	 * @param  boolean  $controller
	 * @return string
	 */
	public function uri($uri = '', $controller = false)
	{
		if ($controller)
			$uri = $this->getControllerPath().'/'.$uri;

		$fullUri = config('cms.base_uri');
		if ($fullUri != "" && $fullUri !== false && !is_null($fullUri))
			$fullUri .= '/'.$uri;
		else
			$fullUri = $uri;

		return $fullUri;
	}

	/**
	 * Create a blog URL.
	 *
	 * @param  string   $uri
	 * @return string
	 */
	public function blogUrl($uri = '')
	{
		return Site::url($this->blogUri($uri), config('blogs.subdomain'));
	}

	/**
	 * Create a blog URI.
	 *
	 * @param  string   $uri
	 * @return string
	 */
	public function blogUri($uri = '')
	{
		$fullUri = config('blogs.base_uri');

		if ($fullUri != "" && $fullUri !== false && !is_null($fullUri))
			$fullUri .= '/'.$uri;
		else
			$fullUri = $uri;

		return $fullUri;
	}

	/**
	 * Create a media URL.
	 *
	 * @param  string   $uri
	 * @return string
	 */
	public function mediaUrl($uri = '')
	{
		return Site::url($this->mediaUri($uri), config('media.subdomain'));
	}

	/**
	 * Create a media URI.
	 *
	 * @param  string   $uri
	 * @return string
	 */
	public function mediaUri($uri = '')
	{
		$fullUri = config('media.base_uri');

		if ($fullUri != "" && $fullUri !== false && !is_null($fullUri))
			$fullUri .= '/'.$uri;
		else
			$fullUri = $uri;

		return $fullUri;
	}

	/**
	 * Modify the current URL by adding the specified page number for pagination links.
	 *
	 * @param  mixed    $page
	 * @return string
	 */
	public function pageUrl($page = 1)
	{
		$urlSegments = explode('/', Request::url());

		if (is_numeric(end($urlSegments)))
		{
			$urlSegments[(count($urlSegments) - 1)] = $page;
		} else {
			if (Site::get('paginationUrlSuffix'))
				$urlSegments[] = Site::get('paginationUrlSuffix');

			$urlSegments[] = $page;
		}

		return implode('/', $urlSegments);
	}

	/**
	 * Set the controller route path.
	 *
	 * @param  mixed    $controller
	 * @return boolean
	 */
	public function setControllerPath($controller)
	{
		if (!is_string($controller))
			$controller = get_class($controller);

		foreach (config('cms.controllers') as $type)
		{
			foreach ($type as $controllerPath => $controllerForRoute)
			{
				if ($controller == $controllerForRoute)
				{
					$this->controllerPath = $controllerPath;
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Get the controller route path.
	 *
	 * @param  mixed    $controller
	 * @return mixed
	 */
	public function getControllerPath($controller = null)
	{
		if (!is_null($controller))
			$this->setRoutePath($controller);

		return $this->controllerPath;
	}

	/**
	 * Set the views location for a controller.
	 *
	 * @param  mixed    $directory
	 * @param  boolean  $root
	 * @return string
	 */
	public function setViewsLocation($directory = null, $root = false)
	{
		if (is_null($directory))
			$directory = Str::plural($this->getContentType());

		$viewsLocationPrefix = !$root ? config('cms.views_location') : null;

		$this->viewsLocation = $directory;

		if (!is_null($viewsLocationPrefix)) {
			if (substr($viewsLocationPrefix, -2) != "::")
				$viewsLocation .= ".";

			$this->viewsLocation = $viewsLocationPrefix.$this->viewsLocation;
		}

		if (substr($this->viewsLocation, -1) != ".")
			$this->viewsLocation .= ".";
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
			return config('cms.views_location').$relativeLocation;
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
	 * Get the media views location for an include.
	 *
	 * @param  string   $relativeLocation
	 * @return string
	 */
	public function mediaView($relativeLocation)
	{
		return $this->view('media.view.'.$relativeLocation, true);
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
	public function modalView($relativeLocation = '', $data = [], $root = false, $returnJson = true)
	{
		if (substr($relativeLocation, 0, 7) != "modals." && !$root)
			$relativeLocation = "modals.".$relativeLocation;

		$response = [
			'title'   => isset($data['title']) ? $data['title'] : '',
			'content' => View::make($this->view($relativeLocation, $root))->with($data)->render(),
			'buttons' => isset($data['buttons']) && $data['buttons'] ? true : false,
		];

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
		if (App::runningInConsole())
			return $default;

		return Setting::value($name, $default);
	}

	/**
	 * Export the settings to a PHP array config file.
	 *
	 * @param  mixed    $settings
	 * @return void
	 */
	public function exportSettings($settings = null)
	{
		$array = Setting::createArray($settings);
		$path  = config_path('exported/cms_settings.php');

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
		return Session::get($name.$this->getContentTypeStudlyCase(), $default);
	}

	/**
	 * Turn a dashed content type like "user-roles" into a studly case content type like "UserRoles".
	 *
	 * @param  mixed    $contentType
	 * @return string
	 */
	public function getContentTypeStudlyCase($contentType = null)
	{
		if (is_null($contentType))
			$contentType = $this->getContentType();

		return studly_case($contentType);
	}

	/**
	 * Turn a dashed content type like "user-roles" into a camelcase content type like "userRoles".
	 *
	 * @param  mixed    $contentType
	 * @return string
	 */
	public function getContentTypeCamelCase($contentType = null)
	{
		if (is_null($contentType))
			$contentType = $this->getContentType();

		return camel_case($contentType);
	}

	/**
	 * Turn a dashed content type like "user-roles" into a snakecase content type like "user_roles".
	 *
	 * @param  mixed    $contentType
	 * @return string
	 */
	public function getContentTypeSnakeCase($contentType = null)
	{
		if (is_null($contentType))
			$contentType = $this->getContentType();

		return snake_case(str_replace('-', '_', $contentType));
	}

	/**
	 * Turn a camelcased string to dashed format.
	 *
	 * @param  string   $string
	 * @return string
	 */
	public function toDashed($string)
	{
		return strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $string));
	}

	/**
	 * Initialize pagination.
	 *
	 * @param  array    $defaultSorting
	 * @param  mixed    $page
	 * @return array
	 */
	public function initPagination($defaultSorting = [], $page = null)
	{
		$contentType = $this->getContentType();

		if (empty($defaultSorting))
			$defaultSorting = Site::get('defaultSorting');

		if (is_null($page))
			$page = !is_null(Input::get('page')) ? Input::get('page') : 1;

		$terms = trim(Input::get('search'));

		$this->pagination = [
			'itemsPerPage' => $this->getSetting('Items Listed Per Page', 20),
			'terms'        => $terms,
			'likeTerms'    => '%'.$terms.'%',
			'filters'      => Input::get('filters'),
			'search'       => $_POST ? true : false,
			'page'         => $page,
			'changingPage' => (bool) Input::get('changing_page'),
			'sortField'    => Input::get('sort_field'),
			'sortOrder'    => (strtolower(Input::get('sort_order')) == 'desc' ? 'desc' : 'asc'),
			'contentType'  => $contentType,
			'result'       => [
				'resultType' => 'Error',
				'messageSet' => false,
			],
		];

		$contentTypeStudlyCase = $this->getContentTypeStudlyCase();

		if ($contentType)
		{
			if ($this->pagination['search'] || $this->pagination['changingPage'])
			{
				Session::set('searchTerms'.$contentTypeStudlyCase, $terms);
				Session::set('searchFilters'.$contentTypeStudlyCase, $this->pagination['filters']);
				Session::set('page'.$contentTypeStudlyCase, $this->pagination['page']);

				Session::set('sortField'.$contentTypeStudlyCase, $this->pagination['sortField']);
				Session::set('sortOrder'.$contentTypeStudlyCase, $this->pagination['sortOrder']);
			} else {
				$this->pagination['terms']     = Session::get('searchTerms'.$contentTypeStudlyCase, $terms);
				$this->pagination['filters']   = Session::get('searchFilters'.$contentTypeStudlyCase);
				$this->pagination['page']      = Session::get('page'.$contentTypeStudlyCase);

				$this->pagination['sortField'] = Session::get('sortField'.$contentTypeStudlyCase);
				$this->pagination['sortOrder'] = Session::get('sortOrder'.$contentTypeStudlyCase);
			}
		}

		// set default sorting
		if (is_null($this->pagination['sortField']) || $this->pagination['sortField'] == "")
		{
			if (is_string($defaultSorting))
				$defaultSorting = [
					'field' => $defaultSorting,
				];

			if (!isset($defaultSorting['field']))
				$defaultSorting['field'] = "id";

			if (!isset($defaultSorting['order']))
				$defaultSorting['order'] = "asc";

			$this->pagination['sortField'] = $defaultSorting['field'];
			$this->pagination['sortOrder'] = (strtolower($defaultSorting['order']) == 'desc' ? 'desc' : 'asc');
		}

		// attempt to disallow sorting on fields that "sortable" is not set for via "tables" config
		$sortableFields = Fractal::getSortableFieldsForTable($contentType);
		if (!in_array($this->pagination['sortField'], $sortableFields))
			$this->pagination['sortField'] = "id";

		$this->setSearchFormDefaults();

		$this->pagination['likeTerms'] = '%'.$this->pagination['terms'].'%';

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
	 * @param  boolean  $initialView
	 * @return array
	 */
	public function setPaginationMessage($initialView = false)
	{
		$contentType = $this->getContentTypeSnakeCase();

		$this->pagination['result']['resultType'] = $this->pagination['content']->total() ? "Success" : "Error";

		if (count($this->pagination['content']))
		{
			if ($this->pagination['changingPage'] || $this->pagination['terms'] == "")
			{
				$this->pagination['result']['message'] = $this->trans('messages.displaying.items_of_total', [
					'start' => $this->pagination['content']->firstItem(),
					'end'   => $this->pagination['content']->lastItem(),
					'total' => $this->pagination['content']->count(),
					'items' => $this->transChoiceLower('labels.'.$contentType, $this->pagination['content']->count()),
				]);
			} else {
				if ($this->pagination['terms'] == "")
					$this->pagination['result']['message'] = $this->trans('messages.search_results_no_terms', [
						'total' => $this->pagination['content']->count(),
						'items' => $this->transChoiceLower('labels.'.$contentType, $this->pagination['content']->count()),
					]);
				else
					$this->pagination['result']['message'] = $this->trans('messages.search_results', [
						'terms' => $this->pagination['terms'],
						'total' => $this->pagination['content']->count(),
						'items' => $this->transChoiceLower('labels.'.$contentType, $this->pagination['content']->count()),
					]);
			}
		} else {
			if (!$initialView)
			{
				if ($this->pagination['terms'] == "")
				{
					if (empty($this->pagination['filters']))
						$this->pagination['result']['message'] = $this->trans('messages.search_no_terms');
					else
						$this->pagination['result']['message'] = $this->trans('messages.search_no_results_no_terms');
				} else {
					$this->pagination['result']['message'] = $this->trans('messages.search_no_results', [
						'terms' => $this->pagination['terms'],
					]);
				}
			}
		}

		$this->pagination['result']['messageSet'] = true;

		return $this->pagination;
	}

	/**
	 * Get the pagination message array.
	 *
	 * @param  boolean  $initialView
	 * @return array
	 */
	public function getPaginationMessageArray($initialView = false)
	{
		if (!isset($this->pagination['result']['message']) && !$this->pagination['result']['messageSet'])
			$this->setPaginationMessage($initialView);

		if (!isset($this->pagination['result']['message']))
			return [];

		return [
			strtolower($this->pagination['result']['resultType']) => $this->pagination['result']['message']
		];
	}

	/**
	 * Set the search form default values and return them.
	 *
	 * @return array
	 */
	public function setSearchFormDefaults()
	{
		$defaults = [
			'search' => $this->pagination['terms'],
		];

		if (!empty($this->pagination['filters']))
		{
			foreach ($this->pagination['filters'] as $field => $value)
			{
				$defaults['filters.'.$field] = $value;
			}
		}

		Form::setDefaults($defaults);

		return $defaults;
	}

	/**
	 * Set the content page.
	 *
	 * @param  integer  $page
	 * @return void
	 */
	public function setPage($page = 1)
	{
		$this->pagination['page'] = $page;

		$this->setRequestedPage();
	}

	/**
	 * Get the requested content page.
	 *
	 * @return integer
	 */
	public function getRequestedPage()
	{
		if (empty($this->pagination))
			return 1;

		return $this->pagination['page'];
	}

	/**
	 * Set the requested content page.
	 *
	 * @return integer
	 */
	public function setRequestedPage()
	{
		$page = $this->getRequestedPage();

		Paginator::currentPageResolver(function() use ($page)
		{
			return $page;
		});
	}

	/**
	 * Get the current content page.
	 *
	 * @return integer
	 */
	public function getCurrentPage()
	{
		if (!isset($this->pagination['content']) || get_class($this->pagination['content']) != "Illuminate\Pagination\LengthAwarePaginator")
			return 1;

		return $this->pagination['content']->currentPage();
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

		return $this->pagination['content']->lastPage();
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
			$name = Fractal::getContentTypeSnakeCase();

		$name = snake_case(str_replace('-', '_', Str::plural($name)));

		$fields      = [];
		$tableConfig = config('tables.'.$name);

		if (empty($tableConfig) || !isset($tableConfig['columns']))
			return $fields;

		foreach ($tableConfig['columns'] as $column)
		{
			if (isset($column['sort']))
			{
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
	public function createTable($content = [], $bodyOnly = false)
	{
		return HTML::table(config('tables.'.Str::plural($this->getContentTypeSnakeCase())), $content, $bodyOnly);
	}

	/**
	 * Render content for a content area.
	 *
	 * @param  string   $content
	 * @param  array    $config
	 * @return string
	 */
	public function renderContent($content, $config = [])
	{
		$configDefault = [
			'contentType'           => 'HTML',
			'contentUrl'            => null,
			'previewOnly'           => false,
			'previewDivider'        => false,
			'viewButton'            => false,
			'viewButtonPlaceholder' => false,
			'viewButtonLabel'       => 'view',
		];

		$config = array_merge($configDefault, $config);

		$viewButtonPlaceholder = "[view-content-button]";

		// replace HTML special character quotation marks with actual quotation marks
		$content = str_replace('&quot;', '"', $content);

		// cut off content after the preview divider for blog articles if preview only option is set
		$previewDivider = config('blogs.preview_divider');
		if ($config['previewOnly'])
		{
			$dividerPosition = strpos($content, $previewDivider);
			if ($dividerPosition)
				$content = substr($content, 0, $dividerPosition);
		}
		else
		{
			$previewDividerMarkup = $config['previewDivider'] ? '<div class="preview-divider">'.$this->trans('labels.preview_divider').'</div>' : '';

			$content = str_replace($previewDivider, $previewDividerMarkup, str_replace("\n".$previewDivider."\n", $previewDividerMarkup, $content));
		}

		// add thumbnail image to content
		if (isset($config['thumbnailImageFileId']) && !is_null($config['thumbnailImageFileId']))
		{
			preg_match_all('/(\[thumbnail\-image)[;]{0,1}([A-Za-z0-9\-\_\.\#\ ]*)\]/', $content, $thumbnailImages);

			if (isset($thumbnailImages[0]) && !empty($thumbnailImages[0]))
			{
				$thumbnailImageText = $thumbnailImages[1][0];

				if (isset($thumbnailImages[2][0]) && $thumbnailImages[2][0] != "")
				{
					$idClasses  = $thumbnailImages[2][0];
					$idClasses  = str_replace('.primary', '', $idClasses);
					$idClasses .= " .primary";

					$content = str_replace($thumbnailImageText, '[image:'.$config['thumbnailImageFileId'], $content);
					$content = str_replace($thumbnailImages[2][0], $idClasses, $content);
				} else {
					$content = str_replace($thumbnailImageText, '[image:'.$config['thumbnailImageFileId'].'; .primary', $content);
				}
			}
		}

		// add file images to content
		$content = $this->renderContentImages($content, '/\!\[file:([0-9]*)[;]{0,1}([\;A-Za-z0-9\-\_\.\#\ ]*)\]/'); //Markdown style image replacement
		$content = $this->renderContentImages($content, '/\[image:([0-9]*)[;]{0,1}([\;A-Za-z0-9\-\_\.\#\ ]*)\]/'); //custom "image" replacement tag to match others

		// add file links to content
		preg_match_all('/\[file:([0-9]*)\]/', $content, $fileIds);

		if (isset($fileIds[0]) && !empty($fileIds[0]))
		{
			$files = ContentFile::whereIn('id', $fileIds[1])->get();

			for ($f = 0; $f < count($fileIds[0]); $f++)
			{
				$name = "";
				$url  = "";

				foreach ($files as $file)
				{
					if ((int) $file->id == (int) $fileIds[1][$f])
					{
						$name = $file->name;
						$url  = $file->getUrl();
					}
				}

				if ($url != "")
				{
					$appUrl = config('app.url');

					if (substr($url, 0, strlen($appUrl)) == $appUrl)
						$link = '<a href="'.$url.'">'.$name.'</a>';
					else
						$link = '<a href="'.$url.'" target="_blank">'.$name.'</a>';

					$content = str_replace($fileIds[0][$f], $link, $content);
				}
			}
		}

		// add file URLs to content
		preg_match_all('/file:([0-9]*)/', $content, $fileIds);

		if (isset($fileIds[0]) && !empty($fileIds[0]))
		{
			$files = ContentFile::whereIn('id', $fileIds[1])->get();

			for ($f = 0; $f < count($fileIds[0]); $f++)
			{
				$url = "";
				foreach ($files as $file)
				{
					if ((int) $file->id == (int) $fileIds[1][$f])
						$url = $file->getUrl();
				}

				if ($url != "")
					$content = str_replace($fileIds[0][$f], $url, $content);
			}
		}

		// add page links to content
		preg_match_all('/\[page:([a-z\-]*)\]/', $content, $pageSlugs);

		if (isset($pageSlugs[0]) && !empty($pageSlugs[0]))
		{
			$pages = Page::whereIn('slug', $pageSlugs[1])->get();

			for ($p = 0; $p < count($pageSlugs[0]); $p++) {
				$title = "";
				$url   = "";
				foreach ($pages as $page)
				{
					if ($page->slug == $pageSlugs[1][$p])
					{
						$title = $page->title;
						$url   = $page->getUrl();
					}
				}

				if ($url != "") {
					$link    = '<a href="'.$url.'">'.$title.'</a>';
					$content = str_replace($pageSlugs[0][$p], $link, $content);
				}
			}
		}

		// add page links to content
		preg_match_all('/page:([a-z\-]*)/', $content, $pageSlugs);

		if (isset($pageSlugs[0]) && !empty($pageSlugs[0]))
		{
			$pages = Page::whereIn('slug', $pageSlugs[1])->get();

			for ($p = 0; $p < count($pageSlugs[0]); $p++)
			{
				$url = "";

				foreach ($pages as $page)
				{
					if ($page->slug == $pageSlugs[1][$p])
						$url = $page->getUrl();
				}

				if ($url != "")
					$content = str_replace($pageSlugs[0][$p], $url, $content);
			}
		}

		// add media items to content
		preg_match_all('/\[media:([0-9]*)\]/', $content, $mediaItemIds);

		if (isset($mediaItemIds[0]) && !empty($mediaItemIds[0]))
		{
			$mediaItems = MediaItem::whereIn('id', $mediaItemIds[1])->get();

			for ($i = 0; $i < count($mediaItemIds[0]); $i++)
			{
				$title = "";
				$url   = "";

				foreach ($mediaItems as $mediaItem)
				{
					if ($mediaItem->id == $mediaItemIds[1][$i])
						$content = str_replace($mediaItemIds[0][$i], $mediaItem->getContent(true), $content);
				}
			}
		}

		// embed YouTube videos in content
		preg_match_all('/\[youtube:([A-Za-z0-9\_\-]{11})\]/', $content, $videos);

		if (isset($videos[0]) && !empty($videos[0]))
		{
			for ($v = 0; $v < count($videos[0]); $v++)
			{
				$video   = $this->getEmbeddedContent('YouTube', $videos[1][$v]);
				$content = str_replace($videos[0][$v], $video, $content);
			}
		}

		preg_match_all('/\[youtube-audio:([A-Za-z0-9\_\-]{11})\]/', $content, $videos);

		if (isset($videos[0]) && !empty($videos[0]))
		{
			for ($v = 0; $v < count($videos[0]); $v++)
			{
				$video   = '<div class="youtube-audio">'.$this->getEmbeddedContent('YouTube', $videos[1][$v]).'</div>';
				$content = str_replace($videos[0][$v], $video, $content);
			}
		}

		// embed Vimeo videos in content
		preg_match_all('/\[vimeo:([0-9]*)\]/', $content, $videos);

		if (isset($videos[0]) && !empty($videos[0]))
		{
			for ($v = 0; $v < count($videos[0]); $v++)
			{
				$video   = $this->getEmbeddedContent('Vimeo', $videos[1][$v]);
				$content = str_replace($videos[0][$v], $video, $content);
			}
		}

		// embed SoundCloud audio in content
		preg_match_all('/\[soundcloud:([0-9]*)\]/', $content, $audioItems);

		if (isset($audioItems[0]) && !empty($audioItems[0]))
		{
			for ($a = 0; $a < count($audioItems[0]); $a++)
			{
				$audioItem = $this->getEmbeddedContent('SoundCloud', $audioItems[1][$a]);
				$content   = str_replace($audioItems[0][$a], $audioItem, $content);
			}
		}

		// render to Markdown
		if (strtolower($config['contentType']) == "markdown")
			$content = Markdown::parse($content);

		// insert quotes
		$quotationLeft  = '<span class="quotation-mark quotation-left">&ldquo;</span>';
		$quotationRight = '<span class="quotation-mark quotation-right">&rdquo;</span>';

		$quotes = [];
		preg_match_all('/\[quotable\](.*?)\[\/quotable\]/', $content, $quotableResult);

		if (isset($quotableResult[0]) && !empty($quotableResult[0]))
		{
			for ($q = 0; $q < count($quotableResult[0]); $q++)
			{
				$content = str_replace($quotableResult[0][$q], $quotableResult[1][$q], $content);
			}

			$quotes = $quotableResult[1];
		}

		preg_match_all('/\[quote:([0-9]*)[;]{0,1}([A-Za-z0-9\-\_\.\#\ ]*)\]/', $content, $quotesResult);

		if (isset($quotesResult[0]) && !empty($quotesResult[0]))
		{
			$ellipsis = '<span class="ellipsis">...</span>';

			for ($q = 0; $q < count($quotesResult[0]); $q++)
			{
				$quoteIndex = (int) $quotesResult[1][$q] - 1;

				$quote = isset($quotes[$quoteIndex]) ? $quotes[$quoteIndex] : "";

				if (!is_null($quote) && $quote != "")
				{
					// add ellipses if necessary
					$quoteStart = substr($quote, 0, 1);
					$quoteEnd   = substr($quote, -1);

					if (strtolower($quoteStart) == $quoteStart)
						$quote = $ellipsis.$quote;

					if (!in_array($quoteEnd, ['.', '?', '!']))
						$quote .= $ellipsis;

					// add ID and/or classes
					$id      = "";
					$classes = ['quote'];
					if (isset($quotesResult[2][$q]) && !empty($quotesResult[2][$q]))
					{
						$idClasses = explode(' ', $quotesResult[2][$q]);

						foreach ($idClasses as $idClass)
						{
							if (substr($idClass, 0, 1) == "#")
								$id = str_replace('#', '', $idClass);
							else if (substr($idClass, 0, 1) == ".")
								$classes[] = str_replace('.', '', $idClass);
						}
					}

					// build quote markup
					$quoteMarkup = '<blockquote';

					if ($id != "")
						$quoteMarkup .= ' id="'.$id.'" ';

					if (!empty($classes))
						$quoteMarkup .= ' class="'.implode(' ', $classes).'" ';

					$quoteMarkup .= '>'.$quotationLeft.trim($quote).$quotationRight.'</blockquote>';

					$quote = $quoteMarkup;
				}

				$content = str_replace($quotesResult[0][$q], $quote, $content);
			}

			$quotes = $quotesResult[1];
		}

		preg_match_all('/\[quote\](.*?)\[\/quote\]/s', $content, $quotesResult);

		if (isset($quotesResult[0]) && !empty($quotesResult[0]))
		{
			for ($q = 0; $q < count($quotesResult[0]); $q++)
			{
				// build quote markup
				$classes     = ['quote'];
				$quoteMarkup = '<blockquote';

				if (!empty($classes))
					$quoteMarkup .= ' class="'.implode(' ', $classes).'" ';

				$quoteMarkup .= '>'.$quotationLeft.'<p>'.trim($quotesResult[1][$q]).'</p>'.$quotationRight.'</blockquote>';

				$quote = $quoteMarkup;

				$content = str_replace($quotesResult[0][$q], $quote, $content);
			}

			$quotes = $quotesResult[1];
		}

		// convert lone ampersands to HTML special characters
		$content = str_replace(' & ', ' &amp; ', $content);

		// add a "View" / "Read More" button
		if ($config['previewOnly'] && $config['viewButton'] && $config['contentUrl'])
		{
			$viewButtonPlaceholderText = $viewButtonPlaceholder;

			if ($config['viewButtonPlaceholder'])
			{
				if (strpos($content, $viewButtonPlaceholderText) === false)
					$content .= $viewButtonPlaceholderText;
			}
			else
			{
				$content .= $this->getViewButtonMarkup($config['contentUrl'], $config['viewButtonLabel']);
			}
		}

		//remove paragraph tags around blockquotes
		$content = str_replace('<p><blockquote', '<blockquote', str_replace('</blockquote></p>', '</blockquote>', $content));

		return $content;
	}

	/**
	 * Render Markdown content for a content area.
	 *
	 * @param  string   $content
	 * @param  array    $config
	 * @return string
	 */
	public function renderMarkdownContent($content, $config = [])
	{
		$config = array_merge($config, ['contentType' => 'Markdown']);

		return $this->renderContent($content, $config);
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

		if (isset($fileIds[0]) && !empty($fileIds[0]))
		{
			$files = ContentFile::whereIn('id', $fileIds[1])->get();

			for ($f = 0; $f < count($fileIds[0]); $f++)
			{
				// add ID and/or classes
				$id        = "";
				$classes   = [];
				$thumbnail = false;
				$modal     = false;
				if (isset($fileIds[2][$f]) && !empty($fileIds[2][$f]))
				{
					$idClasses = explode(' ', $fileIds[2][$f]);

					foreach ($idClasses as $idClass)
					{
						if (strpos($idClass, 'thumb') !== false)
							$thumbnail = true;

						if ($idClass == '.modal-image')
							$modal = true;

						if (substr($idClass, 0, 1) == "#")
							$id = str_replace('#', '', $idClass);
						else if (substr($idClass, 0, 1) == ".")
							$classes[] = str_replace('.', '', $idClass);
					}
				}

				if ($modal && !in_array('image', $classes))
					$classes[] = "image";

				// add name and URL of image
				$name        = "";
				$url         = "";
				$urlFullSize = "";

				foreach ($files as $file)
				{
					if ((int) $file->id == (int) $fileIds[1][$f])
					{
						$name        = $file->name;
						$url         = $file->getUrl($thumbnail);
						$urlFullSize = $file->getUrl();
					}
				}

				if ($url != "")
				{
					$image = '<img src="'.$url.'" alt="'.$name.'" title="'.$name.'" ';

					if ($modal)
						$html = '<a href="'.$urlFullSize.'" ';
					else
						$html = $image;

					if ($id != "")
						$html .= 'id="'.$id.'" ';

					if (!empty($classes))
						$html .= 'class="'.implode(' ', $classes).'" ';

					if ($modal)
						$html .= '>'.$image.' /></a>';
					else
						$html .= '/>';

					$content = str_replace($fileIds[0][$f], $html, $content);
				}
			}
		}

		return $content;
	}

	/**
	 * Render views to content.
	 *
	 * @param  string   $content
	 * @param  boolean  $renderViews
	 * @return string
	 */
	public function renderContentViews($content, $renderViews = true)
	{
		preg_match_all('/\<p\>\[view:\&quot\;([a-z\:\.\_\-]*)\&quot\;\]\<\/p\>/', $content, $views);

		if (isset($views[0]) && !empty($views[0]))
		{
			for ($v = 0; $v < count($views[0]); $v++)
			{
				if ($renderViews)
				{
					$view = $views[1][$v];

					if (View::exists($view))
						$viewHtml = View::make($view)->render();
					else
						$viewHtml = "";

					$content = str_replace($views[0][$v], $viewHtml, $content);
				} else
				{
					$content = str_replace($views[0][$v], '', $content);
				}
			}
		}

		return $content;
	}

	/**
	 * Get the markup for the "Read More" button.
	 *
	 * @param  string   $uri
	 * @param  string   $label
	 * @return string
	 */
	public function getViewContentButtonMarkup($url, $label = 'view')
	{
		return '<a href="'.$url.'" class="btn btn-default btn-xs btn-view-content">'.Fractal::trans('labels.'.$label).'</a>';
	}

	/**
	 * Get the markup for the "Read More" button.
	 *
	 * @param  string   $uri
	 * @param  string   $label
	 * @return string
	 */
	public function addViewButtonToContent($content, $config)
	{
		$configDefault = [
			'contentUrl'      => Request::url(),
			'viewButton'      => false,
			'viewButtonLabel' => 'view',
		];

		$config = array_merge($configDefault, $config);

		$viewButtonMarkup = $config['viewButton'] ? Fractal::getViewContentButtonMarkup($config['contentUrl'], $config['viewButtonLabel']) : "";

		return str_replace('[view-content-button]', $viewButtonMarkup, $content);
	}

	/**
	 * Get the markup for embedded content.
	 *
	 * @param  string   $type
	 * @param  string   $uri
	 * @return string
	 */
	public function getEmbeddedContent($type, $uri)
	{
		return View::make(Fractal::view('partials.embedded.'.strtolower($type), true))->with('uri', $uri)->render();
	}

	/**
	 * Repopulate form field values with content saved in user state if any exists.
	 *
	 * @return boolean
	 */
	public function restoreSavedContent()
	{
		$contentType  = $this->getContentTypeCamelCase();
		$savedContent = \Auth::getState('savedContent.'.$contentType);

		if (!is_null($savedContent))
		{
			if (isset($savedContent->content_areas))
				Form::setDefaults($savedContent, 'content_areas');
			else
				Form::setDefaults($savedContent);

			return true;
		}

		return false;
	}

	/**
	 * Clear saved content for a content type.
	 *
	 * @return boolean
	 */
	public function clearSavedContent()
	{
		$contentType = $this->getContentTypeCamelCase();

		return Auth::removeState('savedContent.'.$contentType);
	}

	/**
	 * Authenticates admin.
	 *
	 * @return boolean
	 */
	public function isAdmin()
	{
		return Auth::is(config('cms.auth_method_admin_role'));
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
			case "Canada":        return $this->trans('labels.province'); break;
			case "United States": return $this->trans('labels.state');    break;
		}

		return $this->trans('labels.region');
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
		return (object) [
			'method'     => $matches[1],
			'parameters' => str_replace("'", '', $matches[2]),
		];
	}

	/**
	 * Export the menus to a PHP array config file.
	 *
	 * @return void
	 */
	public function exportMenus()
	{
		$menus = Menu::orderBy('cms', 'desc')->orderBy('name')->get();
		$array = [];

		foreach ($menus as $menu)
		{
			$array[str_replace(' ', '_', strtolower($menu->name))] = $menu->createArray(false, true);
		}

		$path = config_path('exported/menus.php');

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
	 * @param  array    $options
	 * @return string
	 */
	public function getMenuMarkup($name = 'Main', $options = [])
	{
		return Menu::createMarkupForMenu($name, $options);
	}

	/**
	 * Get menu item selected class.
	 *
	 * @param  array    $name
	 * @return string
	 */
	public function setMenuItemSelectedClass($menuItem)
	{
		$class = MenuItem::setSelectedClass($menuItem);

		$menuItem->class = $class;

		$class = MenuItem::setOpenClass($menuItem);

		return $class;
	}

	/**
	 * Get the visibility status of a menu item.
	 *
	 * @param  object   $menuItem
	 * @return boolean
	 */
	public function isMenuItemVisible($menuItem)
	{
		if (get_class($menuItem) == "Regulus\Fractal\Models\Content\MenuItem")
			$menuItem = $menuItem->createObject();

		$visible = true;

		if (!(bool) $menuItem->active)
			return false;

		if ($menuItem->type == "Content Page" && is_null($menuItem->page_published_date) || strtotime($menuItem->page_published_date) > time())
			return false;

		if ($menuItem->auth_status)
		{
			if (Auth::check() && (int) $menuItem->auth_status == 2)
				return false;

			if (!Auth::check() && (int) $menuItem->auth_status == 1)
				return false;
		}

		if (!empty($menuItem->children) || $menuItem->children_exist)
		{
			$visible = false;

			foreach ($menuItem->children as $subMenuItem)
			{
				if (is_array($subMenuItem))
					$subMenuItem = (object) $subMenuItem;

				if (Auth::hasAccess($subMenuItem->url))
					$visible = true;
			}
		}
		else
		{
			if (!Auth::hasAccess($menuItem->url))
				return false;
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

		return [];
	}

	/**
	 * Get the latest blog article and media item content and organize it into a common array.
	 *
	 * @param  string   $layout
	 * @return array
	 */
	public function getLatestContent()
	{
		$latestContentItemsListed = $this->getSetting('Latest Content Items Listed', 5);

		$content = [];

		$articles = BlogArticle::onlyPublished()->orderBy('published_at', 'desc')->take($latestContentItemsListed)->get();
		foreach ($articles as $article)
		{
			$content[$article->published_at.' - A'] = (object) [
				'type'                => 'Article',
				'type_label'          => Fractal::trans('labels.article'),
				'title'               => $article->getTitle(),
				'url'                 => $article->getUrl(),
				'thumbnail_image_url' => null,
				'content'             => $article->getRenderedContent(['previewOnly' => true]),
				'published_at'        => $article->published_at,
				'date_created'        => null,
			];
		}

		$mediaItems = MediaItem::onlyPublished()->orderBy('published_at', 'desc')->take($latestContentItemsListed)->get();
		foreach ($mediaItems as $mediaItem)
		{
			$content[$mediaItem->published_at.' - I'] = (object) [
				'type'                => 'Media Item',
				'type_label'          => Fractal::trans('labels.mediaItem'),
				'title'               => $mediaItem->getTitle(),
				'url'                 => $mediaItem->getUrl(),
				'thumbnail_image_url' => $mediaItem->getImageUrl(true),
				'content'             => $mediaItem->getRenderedDescription(['previewOnly' => true, 'insertViews' => false]),
				'published_at'        => $mediaItem->published_at,
				'date_created'        => $mediaItem->date_created,
			];
		}

		// sort by descending keys
		krsort($content);

		// limit number of content items
		$content = array_slice($content, 0, $latestContentItemsListed);

		return $content;
	}

	/**
	 * Add an item to the breadcrumb trail.
	 *
	 * @param  string   $title
	 * @param  string   $uri
	 * @return void
	 */
	public function addTrailItem($title = '', $uri = null)
	{
		if (!is_null($uri) && substr($uri, 0, 7) != "http://" && substr($uri, 0, 8) != "https://")
			$uri = $this->uri($uri);

		Site::addTrailItem($title, $uri);
	}

	/**
	 * Add items to the breadcrumb trail.
	 *
	 * @param  array    $items
	 * @return void
	 */
	public function addTrailItems($items)
	{
		foreach ($items as $item) {
			$this->addTrailItem($item);
		}
	}

	/**
	 * Add a button to the button list.
	 *
	 * @param  mixed    $label
	 * @param  string   $uri
	 * @return void
	 */
	public function addButton($label = '', $uri = null)
	{
		if (is_array($label) && isset($label['uri']) && !is_null($label['uri'])) {
			$uri = $this->uri($label['uri']);
		} else {
			if (!is_null($uri))
				$uri = $this->uri($uri);
		}

		$fullUri = config('cms.base_uri');
		if (!is_null($uri) && $fullUri != "")
			$uri = str_replace($fullUri.'/'.$fullUri.'/', $fullUri.'/', $uri);

		if (Auth::hasAccess($this->url($uri)))
			Site::addButton($label, $uri);
	}

	/**
	 * Add buttons to the button list.
	 *
	 * @param  array    $buttons
	 * @return void
	 */
	public function addButtons($buttons)
	{
		foreach ($buttons as $button) {
			$this->addButton($button);
		}
	}

	/**
	 * Get an image URL from a config item.
	 *
	 * @param  string   $item
	 * @return void
	 */
	public function getImageUrlFromConfig($item)
	{
		$path = config($item);
		if (!is_string($path))
			return null;

		$path = explode('::', $path);
		if (count($path) == 2)
			return Site::img($path[1], $path[0]);

		return Site::img($path[0]);
	}

	/**
	 * Get the URL for the Twitter share button.
	 *
	 * @return string
	 */
	public function getTwitterShareUrl()
	{
		$url  = 'https://twitter.com/share?counturl='.urlencode(Site::get('contentUrl', Request::url()));
		$url .= '&text='.urlencode(Site::heading());

		$relatedAccounts = config('social.twitter_related_accounts');
		if (!is_null($relatedAccounts))
		{
			if (is_array($relatedAccounts))
				$relatedAccounts = implode(',', $relatedAccounts);

			$url .= '&related='.$relatedAccounts;
		}

		return $url;
	}

	/**
	 * Get the date format.
	 *
	 * @return string
	 */
	public function getDateFormat()
	{
		return config('format.defaults.date');
	}

	/**
	 * Get the date-time format.
	 *
	 * @return string
	 */
	public function getDateTimeFormat()
	{
		return config('format.defaults.datetime');
	}

	/**
	 * Check whether a date is set.
	 *
	 * @param  string   $date
	 * @return boolean
	 */
	public function dateSet($date)
	{
		return $date != "0000-00-00" && !is_null($date);
	}

	/**
	 * Check whether a date-time is set.
	 *
	 * @param  string   $dateTime
	 * @return boolean
	 */
	public function dateTimeSet($dateTime)
	{
		return $dateTime != "0000-00-00 00:00:00" && !is_null($dateTime);
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

	/**
	 * Get a language item from Fractal's language arrays.
	 *
	 * @param  string   $key
	 * @param  array    $replace
	 * @param  mixed    $locale
	 * @return string
	 */
	function trans($key, array $replace = [], $locale = null)
	{
		if (!config('cms.external_language'))
			$key = 'fractal::'.$key;

		return trans($key, $replace, $locale);
	}

	/**
	 * Get a language item from Fractal's language arrays and make it lowercase.
	 *
	 * @param  string   $key
	 * @param  array    $replace
	 * @param  mixed    $locale
	 * @return string
	 */
	function transLower($key, array $replace = [], $locale = null)
	{
		return strtolower($this->trans($key, $replace, $locale));
	}

	/**
	 * Get a language item from Fractal's language arrays and add "a" or "an" prefix.
	 *
	 * @param  string   $key
	 * @param  array    $replace
	 * @param  mixed    $locale
	 * @return string
	 */
	function transA($key, array $replace = [], $locale = null)
	{
		return Format::a($this->trans($key, $replace, $locale));
	}

	/**
	 * Get a language item from Fractal's language arrays, make it lowercase, and add "a" or "an" prefix.
	 *
	 * @param  string   $key
	 * @param  array    $replace
	 * @param  mixed    $locale
	 * @return string
	 */
	function transLowerA($key, array $replace = [], $locale = null)
	{
		return Format::a($this->transLower($key, $replace, $locale));
	}

	/**
	 * Get a language item from Fractal's language arrays and make it plural.
	 *
	 * @param  string   $key
	 * @param  array    $replace
	 * @param  mixed    $locale
	 * @return string
	 */
	function transPlural($key, array $replace = [], $locale = null)
	{
		return Str::plural($this->trans($key, $replace, $locale));
	}

	/**
	 * Get a language item from Fractal's language arrays, make it lowercase, and make it plural.
	 *
	 * @param  string   $key
	 * @param  array    $replace
	 * @param  mixed    $locale
	 * @return string
	 */
	function transPluralLower($key, array $replace = [], $locale = null)
	{
		return Str::plural($this->transLower($key, $replace, $locale));
	}

	/**
	 * Get a language item according to an integer value.
	 *
	 * @param  string  $key
	 * @param  int     $number
	 * @param  array   $replace
	 * @param  string  $locale
	 * @return string
	 */
	public function transChoice($key, $number = 1, array $replace = [], $locale = null)
	{
		if (!config('cms.external_language'))
			$key = 'fractal::'.$key;

		return trans_choice($key, $number, $replace, $locale);
	}

	/**
	 * Get a language item according to an integer value and add "a" or "an" prefix.
	 *
	 * @param  string  $key
	 * @param  int     $number
	 * @param  array   $replace
	 * @param  string  $locale
	 * @return string
	 */
	public function transChoiceA($key, $number = 1, array $replace = [], $locale = null)
	{
		return Format::a($this->transChoice($key, $number, $replace, $locale));
	}

	/**
	 * Get a language item according to an integer value and make it lowercase.
	 *
	 * @param  string  $key
	 * @param  int     $number
	 * @param  array   $replace
	 * @param  string  $locale
	 * @return string
	 */
	public function transChoiceLower($key, $number = 1, array $replace = [], $locale = null)
	{
		return strtolower($this->transChoice($key, $number, $replace, $locale));
	}

	/**
	 * Get a language item according to an integer value, make it lowercase, and add "a" or "an" prefix.
	 *
	 * @param  string  $key
	 * @param  int     $number
	 * @param  array   $replace
	 * @param  string  $locale
	 * @return string
	 */
	public function transChoiceLowerA($key, $number = 1, array $replace = [], $locale = null)
	{
		return Format::a(strtolower($this->transChoice($key, $number, $replace, $locale)));
	}

	/**
	 * Get an array of language key options.
	 *
	 * @param  mixed   $set
	 * @param  mixed   $prefix
	 * @return array
	 */
	public function getLanguageKeyOptions($set = 'labels', $prefix = null)
	{
		$options = [];

		if (is_string($set))
			$set = $this->trans($set);

		foreach ($set as $key => $item)
		{
			if (is_array($item))
			{
				$options = array_merge($options, $this->getLanguageKeyOptions($item, $key.'.'));
			}
			else
			{
				$itemMultiple = explode('|', $item);

				if (count($itemMultiple) == 2)
				{
					$options['singular:'.$prefix.$key] = $itemMultiple[0];
					$options['plural:'.$prefix.$key] = $itemMultiple[1];
				}
				else
				{
					$options[$prefix.$key] = $item;
				}
			}
		}

		asort($options);

		return $options;
	}

}