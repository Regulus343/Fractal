<?php namespace Regulus\Fractal\Models\User;

use Illuminate\Support\Facades\DB;

use Fractal;

class User extends \Regulus\Identify\Models\User {

	/**
	 * Get user search results.
	 *
	 * @param  array    $searchData
	 * @return Collection
	 */
	public static function getSearchResults($searchData)
	{
		$users = static::orderBy($searchData['sortField'], $searchData['sortOrder']);

		if ($searchData['sortField'] == "last_name")
			$users->orderBy('first_name', $searchData['sortOrder']);

		if ($searchData['sortField'] != "id")
			$users->orderBy('id', 'asc');

		if ($searchData['terms'] != "")
			$users->where(function($query) use ($searchData) {
				$query
					->where('username', 'like', $searchData['likeTerms'])
					->orWhere('first_name', 'like', $searchData['likeTerms'])
					->orWhere('last_name', 'like', $searchData['likeTerms'])
					->orWhere(DB::raw('concat_ws(\' \', first_name, last_name)'), 'like', $searchData['likeTerms'])
					->orWhere('email', 'like', $searchData['likeTerms']);
			});

		Fractal::setRequestedPage();

		return $users->paginate($searchData['itemsPerPage']);
	}

}