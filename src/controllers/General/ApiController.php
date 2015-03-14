<?php namespace Regulus\Fractal\Controllers\General;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;

use Fractal;

use \Auth;

use Regulus\Fractal\Models\Content\File as ContentFile;
use Regulus\Fractal\Models\Media\Item as MediaItem;

use Regulus\Fractal\Controllers\BaseController;

class ApiController extends BaseController {

	public function postSetUserState()
	{
		return (int) Auth::setState(Input::get('name'), Input::get('state'));
	}

	public function postRemoveUserState()
	{
		return (int) Auth::removeState(Input::get('name'), Input::get('state'));
	}

	public function postAutoSave()
	{
		$contentType = Input::get('content_type');
		switch($contentType)
		{
			case "blog-article":
				if (Input::get('id') == "")
				{
					$mainContentArea = Input::get('content_areas.1');
					unset($mainContentArea['id']);

					Auth::setState('autoSavedContent.blogArticle', $mainContentArea);
				}

				return 1;
				break;
		}

		return 0;
	}

	public function postSelectFileMediaItem()
	{
		$type = Input::get('type') == "Media Item" ? Input::get('type') : 'File';

		if ($type == "Media Item")
		{
			$data = [
				'title' => Fractal::trans('labels.select_item', ['item' => Fractal::transChoice('labels.media_item')]),
				'type'  => $type,
				'items' => MediaItem::orderBy('title')->get(),
			];
		} else { //"File"
			$data = [
				'title' => Fractal::trans('labels.select_item', ['item' => Fractal::transChoice('labels.file')]),
				'type'  => $type,
				'items' => ContentFile::orderBy('name')->get(),
			];
		}

		return Fractal::modalView('partials.modals.select_file_media_item', $data, true);
	}

	public function getViewMarkdownGuide()
	{
		return Fractal::modalView('partials.modals.markdown_guide', ['title' => Fractal::trans('labels.markdownGuide')], true);
	}

}