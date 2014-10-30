<?php namespace Regulus\Fractal\Controllers\Media;

use \BaseController;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

use Fractal;

use Regulus\Fractal\Models\MediaItem;
use Regulus\Fractal\Models\MediaType;
use Regulus\Fractal\Models\MediaSet;

use Regulus\ActivityLog\Activity;
use \Auth;
use \Form;
use \Format;
use \Site;

class ViewController extends BaseController {

	public function __construct()
	{
		Site::setMulti(['section', 'title'], Fractal::lang('labels.media'));

		Fractal::setViewsLocation('media.view');

		Site::addTrailItems([
			Fractal::lang('labels.home') => Site::rootUrl(),
			Fractal::lang('labels.media') => Fractal::mediaUrl(),
		]);
	}

	public function getIndex()
	{
		Site::set('mediaList', true);
		Site::set('contentColumnWidth', 9);

		$media = MediaItem::orderBy('published_at', 'desc');

		if (Auth::isNot('admin'))
			$media->onlyPublished();

		$media = $media->get();

		return View::make(Fractal::view('home'))
			->with('media', $media);
	}

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
			$mediaItem = MediaItem::where('id', $slug);

			if (Auth::isNot('admin'))
				$mediaItem->onlyPublished();

			$mediaItem = $mediaItem->first();

			//if item is found by ID, redirect to URL with slug
			if (!empty($mediaItem))
				return Redirect::to($mediaItem->getUrl());
		}

		$mediaItem = MediaItem::where('slug', $slug);

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

		$media = MediaItem::orderBy('published_at', 'desc');

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

}