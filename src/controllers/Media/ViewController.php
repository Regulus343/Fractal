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
		$section = "Media";
		Site::setMulti(['section', 'title'], $section);

		Site::set('menus', 'Front');

		Fractal::setViewsLocation('media.view');
	}

	public function getIndex()
	{
		Site::set('hideTitle', true);
		Site::set('mediaList', true);

		$media = MediaItem::orderBy('published_at', 'desc');

		if (Auth::isNot('admin'))
			$media->onlyPublished();

		$media = $media->get();

		return View::make(Fractal::view('home'))
			->with('media', $media);
	}

	public function getItem($slug)
	{
		Site::set('hideTitle', true);

		$mediaItem = MediaItem::where('slug', $slug);

		if (Auth::isNot('admin'))
			$mediaItem->onlyPublished();

		$mediaItem = $mediaItem->first();

		if (empty($mediaItem))
			return Redirect::to('');

		Site::setMulti(['section', 'subSection', 'title', 'mediaItemTitle'], $mediaItem->title);

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

	public function getI($slug) {
		return $this->getItem($slug);
	}

}