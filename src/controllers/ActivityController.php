<?php namespace Regulus\Fractal;

use \BaseController;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL;

use Aquanode\Formation\Formation as Form;
use Regulus\ActivityLog\Activity;
use Regulus\SolidSite\SolidSite as Site;
use Regulus\TetraText\TetraText as Format;

class ActivityController extends BaseController {

	public function __construct()
	{
		Site::set('section', 'Users');

		$subSection = "User Activity";
		Site::setMulti(array('subSection', 'title'), $subSection);

		Fractal::setContentType('activity');

		Site::set('defaultSorting', array('order' => 'desc'));

		Fractal::setViewsLocation('users.activity');
	}

	public function getIndex()
	{
		$data = Fractal::setupPagination();

		$activities = Activity::orderBy($data['sortField'], $data['sortOrder']);
		if ($data['sortField'] != "id") $activities->orderBy('id', 'asc');
		if ($data['terms'] != "") {
			$activities->where(function($query) use ($data) {
				$query
					->where('description', 'like', $data['likeTerms'])
					->orWhere('details', 'like', $data['likeTerms']);
			});
		}
		$activities = $activities->paginate($data['itemsPerPage']);

		Fractal::setContentForPagination($activities);

		$data = Fractal::setPaginationMessage();
		$messages['success'] = $data['result']['message'];

		$defaults = array(
			'search' => $data['terms']
		);
		Form::setDefaults($defaults);

		return View::make(Fractal::view('list'))
			->with('content', $activities)
			->with('messages', $messages);
	}

	public function postSearch()
	{
		$data = Fractal::setupPagination();

		$activities = Activity::orderBy($data['sortField'], $data['sortOrder']);
		if ($data['sortField'] != "id") $activities->orderBy('id', 'asc');
		if ($data['terms'] != "") {
			$activities->where(function($query) use ($data) {
				$query
					->where('description', 'like', $data['likeTerms'])
					->orWhere('details', 'like', $data['likeTerms']);
			});
		}
		$activities = $activities->paginate($data['itemsPerPage']);

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