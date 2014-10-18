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

class ActivityController extends BaseController {

	public function __construct()
	{
		parent::__construct();

		Site::set('section', 'Users');

		$subSection = "User Activity";
		Site::setMulti(array('subSection', 'title'), $subSection);

		Fractal::setContentType('activities');

		Site::set('defaultSorting', array('order' => 'desc'));

		Fractal::setViewsLocation('users.activity');

		Fractal::addTrailItem('Activity', 'users/activity');
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
			if ($data['terms'] == "") $result['message'] = Lang::get('fractal::messages.searchNoTerms');
		}

		$data['result']['pages']     = Fractal::getLastPage();
		$data['result']['tableBody'] = Fractal::createTable($data['content'], true);

		return $data['result'];
	}

}