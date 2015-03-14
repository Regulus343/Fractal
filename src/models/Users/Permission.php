<?php namespace Regulus\Fractal\Models\Users;

class Permission extends \Regulus\Identify\Permission {

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
					->where('name', 'like', $searchData['likeTerms'])
					->orWhere('description', 'like', $searchData['likeTerms']);
			});

		return $roles->where('deleted_at', null)->paginate($searchData['itemsPerPage']);
	}

}