<?php namespace Regulus\Fractal\Controllers\Media;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Config;
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

use Regulus\ActivityLog\Models\Activity;
use Auth;
use Form;
use Format;
use Site;

class ViewController extends Controller {

	public function __construct()
	{
		Site::set('public', true);

		Site::setMulti(['section', 'title.main'], Fractal::trans('labels.media'));

		// set content type and views location
		Fractal::setContentType('media-item');

		Fractal::setViewsLocation(config('media.views_location'), true);

		Site::addTrailItems([
			Fractal::trans('labels.home')  => Site::rootUrl(),
			Fractal::trans('labels.media') => Fractal::mediaUrl(),
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

	public function getP($page = 1)
	{
		return $this->getItems($page);
	}

	public function getItems($page = 1)
	{
		Site::set('mediaList', true);
		Site::set('contentColumnWidth', 9);
		Site::set('paginationUrlSuffix', 'page');
		Site::set('currentPage', $page);

		Fractal::setPage($page);

		Site::set('subSection', 'All');

		$mediaItems = Item::orderBy('published_at', 'desc');

		if (Auth::isNot('admin'))
			$mediaItems->onlyPublished();

		$mediaItems = $mediaItems->paginate(Fractal::getSetting('Media Items Listed Per Page', 10));

		Site::set('lastPage', $mediaItems->lastPage());

		return View::make(Fractal::view('list'))
			->with('mediaItems', $mediaItems);
	}

	/* Item */

	public function getItem($slug = null)
	{
		if (is_null($slug))
			return Redirect::to(Fractal::mediaUrl())->with('messages', [
				'error' => Fractal::trans('messages.errors.not_found', ['item' => Fractal::transChoiceLower('labels.media_item')])
			]);

		// allow item selection by ID for to allow shorter URLs
		if (is_numeric($slug))
		{
			$mediaItem = Item::where('id', $slug)->onlyPublished(true)->first();

			// if item is found by ID, redirect to URL with slug
			if (!empty($mediaItem))
				return Redirect::to($mediaItem->getUrl());
		}

		$mediaItem = Item::findBySlug($slug, true)->first();

		// if item is not found, check slug without dashes
		if (empty($mediaItem))
		{
			$mediaItem = Item::findByDashlessSlug($slug, true)->onlyPublished(true)->first();

			// if item is found by dashless slug, redirect to URL with proper slug
			if (!empty($mediaItem))
				return Redirect::to($mediaItem->getUrl());
		}

		if (empty($mediaItem) || (!Auth::is('admin') && !$mediaItem->isPublished()))
			return Redirect::to(Fractal::mediaUrl())->with('messages', [
				'error' => Fractal::trans('messages.errors.not_found', ['item' => Fractal::transChoiceLower('labels.media_item')])
			]);

		if ($mediaItem->type)
			Site::setMulti(['subSection', 'mediaType'], $mediaItem->type);

		Site::setMulti(['title.main', 'title.mediaItem'], $mediaItem->getTitle());
		Site::set('contentColumnWidth', 9);
		Site::set('pageIdentifier', 'media-item/'.$mediaItem->slug);

		Site::set('contentUrl', $mediaItem->getUrl());
		Site::set('contentImage', $mediaItem->getThumbnailImageUrl());
		Site::set('contentDescription', strip_tags($mediaItem->getRenderedDescription(['previewOnly' => true])));

		Site::addTrailItem(strip_tags($mediaItem->getTitle()), $mediaItem->getUrl());

		$mediaItem->logView();

		$mediaItems = Item::query()
			->where('id', '<=', ((int) $mediaItem->id + 3))
			->orderBy('published_at', 'desc')
			->take(8);

		if (Auth::isNot('admin'))
			$mediaItems->onlyPublished();

		$mediaItems = $mediaItems->get();

		$messages = [];
		if (!$mediaItem->isPublished()) {
			if ($mediaItem->isPublishedFuture())
				$messages['info'] = Fractal::trans('messages.not_published_until', [
					'item'     => strtolower(Fractal::trans('labels.page')),
					'dateTime' => $mediaItem->getPublishedDateTime(),
				]);
			else
				$messages['info'] = Fractal::trans('messages.not_published', ['item' => Fractal::transChoiceLower('labels.media_item')]);
		}

		return View::make(Fractal::view('item'))
			->with('mediaItem', $mediaItem)
			->with('mediaItems', $mediaItems)
			->with('messages', $messages);
	}

	public function getI($slug = null) {
		return $this->getItem($slug);
	}

	/* Items List for Set */

	public function getSet($slug = null) {
		if (is_null($slug))
			return Redirect::to(Fractal::mediaUrl())->with('messages', [
				'error' => Fractal::trans('messages.errors.not_found', ['item' => Fractal::transChoiceLower('labels.media_set')])
			]);

		Site::set('mediaList', true);
		Site::set('contentColumnWidth', 9);

		$mediaSet = Set::findBySlug($slug);
		if (empty($mediaSet))
			return Redirect::to(Fractal::mediaUrl())->with('messages', [
				'error' => Fractal::trans('messages.errors.not_found', ['item' => Fractal::transChoiceLower('labels.media_set')])
			]);

		Site::setMulti(['title.main', 'subSection', 'mediaSet'], $mediaSet->title);

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
				'error' => Fractal::trans('messages.errors.no_items', [
					'item'  => Fractal::transLower('labels.media_set'),
					'items' => Fractal::transChoiceLower('labels.media_item', 2),
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

		Site::set('contentUrl', $mediaSet->getUrl());
		Site::set('contentDescription', strip_tags($mediaSet->getRenderedDescription(true)));

		if (!empty($mediaItems))
			Site::set('contentImage', $mediaItems[0]->getThumbnailImageUrl());

		Site::set('imageGallery', $mediaSet->image_gallery);

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
				'error' => Fractal::trans('messages.errors.not_found', ['item' => Fractal::transChoiceLower('labels.media_type')])
			]);

		Site::set('mediaList', true);
		Site::set('contentColumnWidth', 9);

		$mediaType = Type::findBySlug($slug);
		if (empty($mediaType))
			return Redirect::to(Fractal::mediaUrl())->with('messages', [
				'error' => Fractal::trans('messages.errors.not_found', ['item' => Fractal::transChoiceLower('labels.media_type')])
			]);

		Site::setMulti(['title.main', 'subSection', 'mediaType'], $mediaType->getName(true));

		$mediaItems = Item::where('media_items.media_type_id', $mediaType->id);

		if (Auth::isNot('admin'))
			$mediaItems->onlyPublished();

		$mediaItems = $mediaItems->get();

		$mediaItemIds = [];
		foreach ($mediaItems as $mediaItem) {
			if (!in_array($mediaItem->id, $mediaItemIds))
				$mediaItemIds[] = $mediaItem->id;
		}

		$messages = [];

		if (!empty($mediaItemIds))
		{
			$mediaItems = Item::whereIn('id', $mediaItemIds)
				->orderBy('published_at', 'desc');

			if (Auth::isNot('admin'))
				$mediaItems->onlyPublished();

			$mediaItems = $mediaItems->get();
		} else {
			$mediaItems = [];

			$messages['error'] = Fractal::trans('messages.errors.no_items', [
				'item'  => Fractal::transChoiceLower('labels.media_type'),
				'items' => Fractal::transChoiceLower('labels.media_item', 2),
			]);
		}

		Site::addTrailItem($mediaType->name, Request::url());

		return View::make(Fractal::view('list'))
			->with('mediaItems', $mediaItems)
			->with('mediaType', $mediaType)
			->with('messages', $messages);
	}

	public function getT($slug = null) {
		return $this->getType($slug);
	}

	/* Log Download */

	public function getLogDownload($id) {
		$mediaItem = Item::where('id', $id)->onlyPublished()->first();

		if (empty($mediaItem))
			return 0;

		$mediaItem->logDownload();
		return 1;
	}

}