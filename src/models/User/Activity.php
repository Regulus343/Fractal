<?php namespace Regulus\Fractal\Models\User;

use Fractal;

class Activity extends \Regulus\ActivityLog\Models\Activity {

	/**
	 * Get activity search results.
	 *
	 * @param  array    $searchData
	 * @return Collection
	 */
	public static function getSearchResults($searchData)
	{
		$activities = static::orderBy($searchData['sortField'], $searchData['sortOrder']);

		if ($searchData['sortField'] != "id")
			$activities->orderBy('id', 'asc');

		if ($searchData['terms'] != "")
			$activities->where(function($query) use ($searchData) {
				$query
					->where('description', 'like', $searchData['likeTerms'])
					->orWhere('details', 'like', $searchData['likeTerms']);
			});

		Fractal::setRequestedPage();

		return $activities->paginate($searchData['itemsPerPage']);
	}

}