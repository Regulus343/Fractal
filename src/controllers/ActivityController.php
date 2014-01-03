<?php namespace Regulus\Fractal;

use \BaseController;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL;

use Aquanode\Elemental\Elemental as HTML;
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

		Fractal::setViewsLocation('users.activity');
	}

	public function getIndex()
	{
		$data = Fractal::setupPagination('Activity');

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

		Fractal::addContentForPagination($activities);

		$data = Fractal::setPaginationMessage();
		$messages['success'] = $data['result']['message'];

		$defaults = array(
			'search' => $data['terms']
		);
		Form::setDefaults($defaults);

		return View::make(Fractal::view('list'))
			->with('activities', $activities)
			->with('contentType', 'activity')
			->with('page', $activities->getCurrentPage())
			->with('lastPage', $activities->getLastPage())
			->with('messages', $messages);
	}

	public function postSearch()
	{
		$data = Fractal::setupPagination('Activity');

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

		Fractal::addContentForPagination($activities);

		if (count($activities)) {
			$data = Fractal::setPaginationMessage();
		} else {
			$data['content'] = User::orderBy('id')->paginate($data['itemsPerPage']);
			if ($terms == "") $result['message'] = Lang::get('fractal::messages.searchNoTerms');
		}

		$data['result']['tableBody'] = HTML::table(Config::get('fractal::tables.activity'), $data['content'], true);

		return $data['result'];
	}

}