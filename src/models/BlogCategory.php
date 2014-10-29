<?php namespace Regulus\Fractal\Models;

use Regulus\Formation\BaseModel;

use Fractal;

class BlogCategory extends BaseModel {

	/**
	 * The database table used by the model.
	 *
	 * @var    string
	 */
	protected $table = 'blog_categories';

	/**
	 * The fillable fields for the model.
	 *
	 * @var    array
	 */
	protected $fillable = [
		'parent_id',
		'slug',
		'name',
	];

	/**
	 * The blog articles that the category belongs to.
	 *
	 * @return Collection
	 */
	public function articles()
	{
		return $this->belongsToMany('Regulus\Fractal\Models\BlogArticle', 'blog_article_categories', 'category_id', 'article_id');
	}

	/**
	 * The number of blog articles that the category belongs to.
	 *
	 * @return integer
	 */
	public function getNumberOfArticles()
	{
		return $this->articles()->count();
	}

	/**
	 * Get category search results.
	 *
	 * @param  array    $searchData
	 * @return Collection
	 */
	public static function getSearchResults($searchData)
	{
		$categories = static::orderBy($searchData['sortField'], $searchData['sortOrder']);

		if ($searchData['sortField'] != "id")
			$categories->orderBy('id', 'asc');

		if ($searchData['terms'] != "")
			$categories->where('name', 'like', $searchData['likeTerms']);

		return $categories->paginate($searchData['itemsPerPage']);
	}

}