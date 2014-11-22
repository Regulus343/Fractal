<?php namespace Regulus\Fractal\Controllers\Media;

use \BaseController;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

use Fractal;

use Regulus\Fractal\Models\Media\Item;
use Regulus\Fractal\Models\Media\Type;
use Regulus\Fractal\Models\Media\Set;

use Regulus\ActivityLog\Activity;
use \Auth;
use \Form;
use \Format;
use \Site;

class ViewController extends BaseController {

	public function __construct()
	{
		Site::setMulti(['section', 'title'], Fractal::lang('labels.media'));

		//set content type and views location
		Fractal::setContentType('media-item');

		Fractal::setViewsLocation('media.view');

		Site::addTrailItems([
			Fractal::lang('labels.home')  => Site::rootUrl(),
			Fractal::lang('labels.media') => Fractal::mediaUrl(),
		]);
	}

	/* Items List */

	public function getIndex()
	{
		return $this->getItems();
	}


	public function getPage($page = 1)
	{
		return $this->getItems($page);
	}

	public function getItems($page = 1)
	{
		Site::set('mediaList', true);
		Site::set('contentColumnWidth', 9);
		Site::set('paginationUrlSuffix', 'page');
		Site::set('currentPage', $page);

		DB::getPaginator()->setCurrentPage($page);

		$mediaItems = Item::orderBy('published_at', 'desc');

		if (Auth::isNot('admin'))
			$mediaItems->onlyPublished();

		$mediaItems = $mediaItems->paginate(Fractal::getSetting('Media Items Listed Per Page', 10));

		Site::set('lastPage', $mediaItems->getLastPage());

		return View::make(Fractal::view('list'))
			->with('mediaItems', $mediaItems);
	}

	/* Item */

	public function getItem($slug = null)
	{
		if (is_null($slug))
			return Redirect::to(Fractal::mediaUrl())->with('messages', [
				'error' => Fractal::lang('messages.errorNotFound', ['item' => Fractal::langLower('labels.mediaItem')])
			]);

		Site::set('contentColumnWidth', 9);

		//allow item selection by ID for to allow shorter URLs
		if (is_numeric($slug))
		{
			$mediaItem = Item::where('id', $slug);

			if (Auth::isNot('admin'))
				$mediaItem->onlyPublished();

			$mediaItem = $mediaItem->first();

			//if item is found by ID, redirect to URL with slug
			if (!empty($mediaItem))
				return Redirect::to($mediaItem->getUrl());
		}

		$mediaItem = Item::where('slug', $slug);

		if (Auth::isNot('admin'))
			$mediaItem->onlyPublished();

		$mediaItem = $mediaItem->first();

		if (empty($mediaItem))
			return Redirect::to(Fractal::mediaUrl())->with('messages', [
				'error' => Fractal::lang('messages.errorNotFound', ['item' => Fractal::langLower('labels.mediaItem')])
			]);

		Site::setMulti(['subSection', 'title', 'mediaItemTitle'], $mediaItem->title);

		Site::addTrailItem($mediaItem->title, $mediaItem->getUrl());

		$mediaItem->logView();

		$media = Item::orderBy('published_at', 'desc');

		if (Auth::isNot('admin'))
			$media->onlyPublished();

		$media = $media->get();

		$messages = [];
		if (!$mediaItem->isPublished()) {
			if ($mediaItem->isPublishedFuture())
				$messages['info'] = Lang::get('fractal::messages.notPublishedUntil', [
					'item'     => strtolower(Lang::get('fractal::labels.page')),
					'dateTime' => $mediaItem->getPublishedDateTime(),
				]);
			else
				$messages['info'] = Lang::get('fractal::messages.notPublished', ['item' => Lang::get('fractal::labels.mediaItem')]);
		}

		return View::make(Fractal::view('item'))
			->with('mediaItem', $mediaItem)
			->with('media', $media)
			->with('messages', $messages);
	}

	public function getI($slug = null) {
		return $this->getItem($slug);
	}

	/* Items List for Set */

	public function getSet($slug = null) {
		if (is_null($slug))
			return Redirect::to(Fractal::mediaUrl())->with('messages', [
				'error' => Fractal::lang('messages.errorNotFound', ['item' => Fractal::langLower('labels.mediaSet')])
			]);

		Site::set('mediaList', true);
		Site::set('contentColumnWidth', 9);

		$mediaSet = Set::findBySlug($slug);
		if (empty($mediaSet))
			return Redirect::to(Fractal::mediaUrl())->with('messages', [
				'error' => Fractal::lang('messages.errorNotFound', ['item' => Fractal::langLower('labels.mediaSet')])
			]);

		$mediaItems = Item::query()
			->select(['media_items.id', 'media_items.published_at'])
			->leftJoin('media_item_sets', 'media_items.id', '=', 'media_item_sets.item_id')
			->leftJoin('media_sets', 'media_item_sets.set_id', '=', 'media_sets.id')
			->where('media_sets.slug', $slug);

		if (Auth::isNot('admin'))
			$mediaItems->onlyPublished();

		$mediaItems = $mediaItems->get();

		$mediaItemIds = [];
		foreach ($mediaItems as $mediaItem) {
			if (!in_array($mediaItem->id, $mediaItemIds))
				$mediaItemIds[] = $mediaItem->id;
		}

		if (empty($mediaItemIds))
			return Redirect::to(Fractal::mediaUrl())->with('messages', [
				'error' => Fractal::lang('messages.errorNoItems', [
					'item'  => Fractal::langLower('labels.mediaSet'),
					'items' => Fractal::langLower('labels.mediaItems'),
				])
			]);

		$mediaItems = Item::query()
			->select(['media_items.*'])
			->leftJoin('media_item_sets', 'media_items.id', '=', 'media_item_sets.item_id')
			->leftJoin('media_sets', 'media_item_sets.set_id', '=', 'media_sets.id')
			->where('media_sets.slug', $slug)
			->orderBy('media_item_sets.display_order');

		if (Auth::isNot('admin'))
			$mediaItems->onlyPublished();

		$mediaItems = $mediaItems->get();

		Site::addTrailItem($mediaSet->title, Request::url());

		return View::make(Fractal::view('list'))
			->with('mediaItems', $mediaItems)
			->with('mediaSet', $mediaSet);
	}

	public function getS($slug = null) {
		return $this->getSet($slug);
	}

	/* Items List for Type */

	public function getType($slug = null) {
		if (is_null($slug))
			return Redirect::to(Fractal::mediaUrl())->with('messages', [
				'error' => Fractal::lang('messages.errorNotFound', ['item' => Fractal::langLower('labels.mediaType')])
			]);

		Site::set('mediaList', true);
		Site::set('contentColumnWidth', 9);

		$mediaType = Type::findBySlug($slug);
		if (empty($mediaType))
			return Redirect::to(Fractal::mediaUrl())->with('messages', [
				'error' => Fractal::lang('messages.errorNotFound', ['item' => Fractal::langLower('labels.mediaType')])
			]);

		$mediaItems = Item::where('media_items.media_type_id', $mediaType->id);

		if (Auth::isNot('admin'))
			$mediaItems->onlyPublished();

		$mediaItems = $mediaItems->get();

		$mediaItemIds = [];
		foreach ($mediaItems as $mediaItem) {
			if (!in_array($mediaItem->id, $mediaItemIds))
				$mediaItemIds[] = $mediaItem->id;
		}

		if (empty($mediaItemIds))
			return Redirect::to(Fractal::mediaUrl())->with('messages', [
				'error' => Fractal::lang('messages.errorNoItems', [
					'item'  => Fractal::langLower('labels.mediaType'),
					'items' => Fractal::langLower('labels.mediaItems'),
				])
			]);

		$mediaItems = Item::whereIn('id', $mediaItemIds)
			->orderBy('published_at', 'desc');

		if (Auth::isNot('admin'))
			$mediaItems->onlyPublished();

		$mediaItems = $mediaItems->get();

		Site::addTrailItem($mediaType->name, Request::url());

		return View::make(Fractal::view('list'))
			->with('mediaItems', $mediaItems)
			->with('mediaType', $mediaType);
	}

	public function getT($slug = null) {
		return $this->getType($slug);
	}

}