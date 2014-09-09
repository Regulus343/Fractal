<?php namespace Regulus\Fractal\Models;

class Role extends \Regulus\Identify\Role {

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
					->orWhere('name', 'like', $searchData['likeTerms']);
			});

		return $roles->paginate($searchData['itemsPerPage']);
	}

}