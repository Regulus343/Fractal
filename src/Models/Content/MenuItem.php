<?php namespace Regulus\Fractal\Models\Content;

use Regulus\Formation\Models\Base;

use Fractal;

use Auth;
use Site;

use Illuminate\Support\Facades\Request;

class MenuItem extends Base {

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
	protected $fillable = [
		'name',
		'cms',
		'menu_id',
		'parent_id',
		'type',
		'page_id',
		'uri',
		'subdomain',
		'label',
		'label_language_key',
		'icon',
		'class',
		'additional_info',
		'display_order',
		'auth_status',
		'active',
	];

	/**
	 * The special typed fields for the model.
	 *
	 * @var    array
	 */
	protected $types = [
		'active' => 'checkbox',
	];

	/**
	 * The special formatted fields for the model for saving to the database.
	 *
	 * @var    array
	 */
	protected $formatsForDb = [
		'parent_id' => 'null-if-blank',
		'page_id'   => 'null-if-blank',
	];

	/**
	 * The menu that the menu item belong to.
	 *
	 * @return Menu
	 */
	public function menu()
	{
		return $this->belongsTo('Regulus\Fractal\Models\Content\Menu');
	}

	/**
	 * The menu items that belong to the menu item.
	 *
	 * @return MenuItem
	 */
	public function parent()
	{
		return $this->belongsTo('Regulus\Fractal\Models\Content\MenuItem', 'parent_id');
	}

	/**
	 * The menu items that belong to the menu item.
	 *
	 * @return Collection
	 */
	public function children()
	{
		return $this->hasMany('Regulus\Fractal\Models\Content\MenuItem', 'parent_id')->orderBy('display_order');
	}

	/**
	 * The page that belongs to the menu item (if applicable).
	 *
	 * @return Page
	 */
	public function page()
	{
		return $this->belongsTo('Regulus\Fractal\Models\Content\Page');
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
	 * Get the label for a menu item.
	 *
	 * @param  boolean  $icon
	 * @return string
	 */
	public function getLabel()
	{
		if (!is_null($this->label_language_key) && $this->label_language_key != "")
		{
			if (substr($this->label_language_key, 0, 7) == "plural:")
				return Fractal::transChoice('labels.'.str_replace('plural:', '', $this->label_language_key), 2);

			elseif (substr($this->label_language_key, 0, 9) == "singular:")
				return Fractal::transChoice('labels.'.str_replace('singular:', '', $this->label_language_key), 1);

			else
				return Fractal::trans('labels.'.$this->label_language_key);
		}

		return $this->label;
	}

	/**
	 * Get the URI for the menu item.
	 *
	 * @return string
	 */
	public function getUri()
	{
		if ($this->menu->cms)
			$uri = config('cms.base_uri');
		else
			$uri = '';

		if ($this->page)
			$uri = $uri != '' ? $uri.'/'.$this->page->slug : $this->page->slug;
		else
			$uri = $uri != '' ? $uri.'/'.$this->uri : $this->uri;

		if (substr($uri, -1) == '/')
			$uri = substr($uri, 0, (strlen($uri) - 1));

		return $uri;
	}

	/**
	 * Get the URL for the menu item.
	 *
	 * @return string
	 */
	public function getUrl()
	{
		return Site::url($this->getUri(), $this->subdomain);
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
	 * Set the "selected" class for the menu item if necessary.
	 *
	 * @param  mixed    $item
	 * @return string
	 */
	public static function setSelectedClass($item)
	{
		$selectedClass = "active";
		$defaultClass  = $item->class;
		$class         = $defaultClass;

		if ((isset($item->parent_id) && ! (int) $item->parent_id) || (isset($item->parentId) && ! (int) $item->parentId))
			$checked = "section";
		else
			$checked = "subSection";

		$class .= Site::selectBy($checked, $item->label, true, $selectedClass);

		// if nothing was found and menu item is for a content page, check its title
		if ($class == $defaultClass && $item->type == "Content Page")
			$class .= Site::selectBy($checked, $item->page, true, $selectedClass);

		if (strpos($class, $selectedClass) === false && Request::url() == $item->url)
		{
			if ($class != "")
				$class .= " ";

			$class .= $selectedClass;
		}

		return trim($class);
	}

	/**
	 * Set the "open" class for the menu item if necessary.
	 *
	 * @param  mixed    $item
	 * @return string
	 */
	public static function setOpenClass($item)
	{
		$openClass    = "open";
		$defaultClass = $item->class;
		$class        = $defaultClass;
		$checked      = Auth::checkState('menuOpen', $item->id);

		if ($checked) {
			if ($class != "")
				$class .= " ";

			$class .= $openClass;
		}

		return trim($class);
	}

	/**
	 * Get the class attribute of the menu item's anchor tag.
	 *
	 * @return string
	 */
	public function getAnchorClass()
	{
		$class = "";
		if (count($this->children))
		{
			if ($class != "")
				$class .= " ";

			$class = "dropdown-toggle";
		}

		return $class;
	}

	/**
	 * Get the icon for a menu item (including an icon if one exists and the icon attribute is set).
	 *
	 * @param  boolean  $icon
	 * @return string
	 */
	public function getIcon()
	{
		if ($this->icon != "")
		{
			$iconElement     = config('html.icon.element');
			$iconClassPrefix = config('html.icon.class_prefix');

			return '<'.$iconElement.' class="'.$iconClassPrefix.$this->icon.'"></i> ';
		}

		return "";
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
		$children = [];

		foreach ($this->children as $child)
		{
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
		return (object) [
			'id'                  => (int) $this->id,
			'parent_id'           => (int) $this->parent_id,
			'type'                => $this->type,
			'url'                 => $this->getUrl(),
			'page'                => $this->type == "Content Page" && $this->page ? $this->page->title : false,
			'page_published_date' => $this->type == "Content Page" && $this->page ? $this->page->published_at : null,
			'label'               => $this->getLabel(),
			'icon'                => $this->getIcon(),
			'class'               => $this->getClass($setSelectedClass),
			'active'              => $this->active,
			'auth_status'         => $this->auth_status,
			'anchor_class'        => $this->getAnchorClass(),
			'children_exist'      => $this->children->count(),
			'children'            => $this->getChildrenArray($setSelectedClass, $ignoreVisibilityStatus),
		];
	}

	/**
	 * Get the last updated date/time.
	 *
	 * @param  mixed    $dateFormat
	 * @return string
	 */
	public function getLastUpdatedDateTime($dateFormat = false)
	{
		if (!$dateFormat)
			$dateFormat = config('format.defaults.datetime');

		return $this->updated_at != "0000-00-00" ? date($dateFormat, strtotime($this->updated_at)) : date($dateFormat, strtotime($this->created_at));
	}

	/**
	 * Get an array of icon options.
	 *
	 * @return array
	 */
	public static function getIconOptions()
	{
		$icons = [
			'home',
			'th',
			'th-large',
			'th-list',
			'search',
			'check',
			'remove',
			'ban',
			'plus',
			'minus',
			'edit',
			'check-circle',
			'minus-circle',
			'plus-square',
			'minus-square',
			'power-off',
			'plug',
			'clock-o',
			'upload',
			'download',
			'book',
			'film',
			'flag',
			'flag-checkered',
			'flag-o',
			'envelope',
			'envelope-o',
			'inbox',
			'trash',
			'refresh',
			'file',
			'file-o',
			'file-image-o',
			'file-audio-o',
			'file-movie-o',
			'file-text-o',
			'tag',
			'tags',
			'pencil',
			'align-left',
			'align-center',
			'align-right',
			'align-justify',
			'map-marker',
			'adjust',
			'tint',
			'signal',
			'share',
			'fast-backward',
			'step-backward',
			'backward',
			'play',
			'pause',
			'stop',
			'forward',
			'step-forward',
			'fast-forward',
			'eject',
			'chevron-left',
			'chevron-right',
			'chevron-up',
			'chevron-down',
			'chevron-circle-left',
			'chevron-circle-right',
			'chevron-circle-up',
			'chevron-circle-down',
			'crosshairs',
			'question',
			'info',
			'exclamation',
			'question-circle',
			'info-circle',
			'exclamation-circle',
			'exclamation-triangle',
			'arrow-left',
			'arrow-right',
			'arrow-up',
			'arrow-down',
			'share-alt',
			'asterisk',
			'gift',
			'leaf',
			'fire',
			'eye',
			'plane',
			'calendar',
			'random',
			'comment',
			'magnet',
			'retweet',
			'shopping-cart',
			'folder',
			'folder-open',
			'bar-chart',
			'camera-retro',
			'key',
			'cog',
			'cogs',
			'comments',
			'comments-o',
			'thumbs-up',
			'thumbs-down',
			'star-o',
			'star-half',
			'star-half-full',
			'star',
			'heart-o',
			'heart',
			'sticky-note',
			'sticky-note-o',
			'thumb-tack',
			'sign-in',
			'sign-out',
			'phone',
			'twitter',
			'facebook',
			'bookmark',
			'bookmark-o',
			'ellipsis-v',
			'trophy',
			'github',
			'slack',
			'lock',
			'unlock',
			'credit-card',
			'rss',
			'rss-square',
			'bullhorn',
			'bell',
			'bell-o',
			'certificate',
			'arrow-circle-left',
			'arrow-circle-right',
			'arrow-circle-up',
			'arrow-circle-down',
			'user',
			'user-plus',
			'user-times',
			'users',
			'globe',
			'wrench',
			'tasks',
			'filter',
			'briefcase',
			'suitcase',
			'group',
			'link',
			'unlink',
			'cloud',
			'cut',
			'copy',
			'paste',
			'save',
			'reorder',
			'list-ul',
			'list-ol',
			'table',
			'magic',
			'truck',
			'google-plus',
			'google',
			'money',
			'caret-left',
			'caret-right',
			'caret-up',
			'caret-down',
			'sort-up',
			'sort',
			'sort-down',
			'dashboard',
			'bolt',
			'sitemap',
			'umbrella',
			'lightbulb-o',
			'cloud-download',
			'cloud-upload',
			'stethoscope',
			'music',
			'coffee',
			'building',
			'hospital-o',
			'ambulance',
			'medkit',
			'beer',
			'quote-left',
			'quote-right',
			'circle',
			'circle-thin',
			'gamepad',
			'code',
			'code-fork',
			'location-arrow',
			'question',
			'info',
			'exclamation',
			'puzzle-piece',
			'shield',
			'rocket',
			'bullseye',
			'ticket',
			'compass',
			'eur',
			'gbp',
			'usd',
			'inr',
			'jpy',
			'cny',
			'krw',
			'btc',
			'youtube',
			'youtube-play',
			'dropbox',
			'tumblr',
			'trello',
			'female',
			'male',
			'sun-o',
			'moon-o',
			'archive',
			'bug',
		];

		$options = [];
		foreach ($icons as $icon)
		{
			$options[$icon] = '<i class="fa fa-'.$icon.'"></i> '.ucwords(str_replace('-', ' ', $icon));
		}

		return $options;
	}

}