<?php namespace Regulus\Fractal\Models\User;

use Fractal;

class Role extends \Regulus\Identify\Models\Role {

	/**
	 * Get user role search results.
	 *
	 * @param  array    $searchData
	 * @return Collection
	 */
	public static function getSearchResults($searchData)
	{
		$roles = static::orderBy($searchData['sortField'], $searchData['sortOrder']);

		if ($searchData['sortField'] != "id")
			$roles->orderBy('id', 'asc');

		if ($searchData['terms'] != "")
			$roles->where(function($query) use ($searchData) {
				$query
					->where('role', 'like', $searchData['likeTerms'])
					->orWhere('name', 'like', $searchData['likeTerms'])
					->orWhere('description', 'like', $searchData['likeTerms']);
			});

		Fractal::setRequestedPage();

		return $roles->where('deleted_at', null)->paginate($searchData['itemsPerPage']);
	}

}