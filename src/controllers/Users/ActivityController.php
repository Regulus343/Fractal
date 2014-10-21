<?php namespace Regulus\Fractal\Controllers\Users;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

use Fractal;

use Regulus\Fractal\Models\Activity;

use \Form;
use \Format;
use \Site;

class ActivityController extends UsersController {

	public function __construct()
	{
		parent::__construct();

		Fractal::setControllerPath($this);

		Site::set('section', 'Users');
		Site::set('subSection', 'Activity');
		Site::set('title', Fractal::lang('labels.userActivity'));

		Fractal::setContentType('activities');

		Site::set('defaultSorting', ['order' => 'desc']);

		Fractal::setViewsLocation('users.activity');

		Fractal::addTrailItem('Activity', Fractal::getControllerPath());
	}

	public function getIndex()
	{
		$data       = Fractal::setupPagination();
		$activities = Activity::getSearchResults($data);

		Fractal::setContentForPagination($activities);

		$data     = Fractal::setPaginationMessage(true);
		$messages = Fractal::getPaginationMessageArray();

		return View::make(Fractal::view('list'))
			->with('content', $activities)
			->with('messages', $messages);
	}

	public function postSearch()
	{
		$data       = Fractal::setupPagination();
		$activities = Activity::getSearchResults($data);

		Fractal::setContentForPagination($activities);

		if (count($activities)) {
			$data = Fractal::setPaginationMessage();
		} else {
			$data['content'] = Activity::orderBy($data['sortField'], $data['sortOrder'])->paginate($data['itemsPerPage']);
			if ($data['terms'] == "") $result['message'] = Fractal::lang('messages.searchNoTerms');
		}

		$data['result']['pages']     = Fractal::getLastPage();
		$data['result']['tableBody'] = Fractal::createTable($data['content'], true);

		return $data['result'];
	}

}