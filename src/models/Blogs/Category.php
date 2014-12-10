<?php namespace Regulus\Fractal\Models\Blogs;

use Regulus\Formation\BaseModel;

use Fractal;

class Category extends BaseModel {

	/**
	 * The database table used by the model.
	 *
	 * @var    string
	 */
	protected $table = 'blog_categories';

	/**
	 * The foreign key for the model.
	 *
	 * @var    string
	 */
	protected $foreignKey = 'category_id';

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
		return $this->belongsToMany('Regulus\Fractal\Models\Blogs\Article', 'blog_article_categories', 'category_id', 'article_id');
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
	 * Get the URL for the category.
	 *
	 * @return string
	 */
	public function getUrl()
	{
		return Fractal::blogUrl('c/'.$this->slug);
	}

	/**
	 * Check whether collection of categories have any published articles.
	 *
	 * @param  Collection $categories
	 * @return boolean
	 */
	public static function publishedArticleInCategories($categories)
	{
		foreach ($categories as $category)
		{
			if ($category->articles()->onlyPublished()->count())
				return true;
		}

		return false;
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