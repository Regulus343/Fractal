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

	public function postSelectFileMediaItem()
	{
		$type = Input::get('type') == "Media Item" ? Input::get('type') : 'File';

		if ($type == "Media Item")
		{
			$data = [
				'title' => Fractal::lang('labels.selectMediaItem'),
				'type'  => $type,
				'items' => MediaItem::orderBy('title')->get(),
			];
		} else { //"File"
			$data = [
				'title' => Fractal::lang('labels.selectFile'),
				'type'  => $type,
				'items' => ContentFile::orderBy('name')->get(),
			];
		}

		return Fractal::modalView('partials.modals.select_file_media_item', $data, true);
	}

}