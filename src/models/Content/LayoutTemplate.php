<?php namespace Regulus\Fractal\Models\Content;

use Regulus\Formation\Models\Base;

use Fractal;

class LayoutTemplate extends Base {

	/**
	 * The database table used by the model.
	 *
	 * @var    string
	 */
	protected $table = 'content_layout_templates';

	/**
	 * The fillable fields for the model.
	 *
	 * @var    array
	 */
	protected $fillable = [
		'name',
		'layout',
		'static',
	];

	/**
	 * Get the validation rules used by the model.
	 *
	 * @param  boolean  $id
	 * @param  string   $type
	 * @return array
	 */
	public static function validationRules($id = false, $type = 'default')
	{
		return [
			'name'   => ['required'],
			'layout' => ['required'],
		];
	}

	/**
	 * The creator of the layout template.
	 *
	 * @return User
	 */
	public function creator()
	{
		return $this->belongsTo(config('auth.model'), 'user_id');
	}

	/**
	 * The pages that belong to the layout template.
	 *
	 * @return Collection
	 */
	public function pages()
	{
		return $this->hasMany('Regulus\Fractal\Models\Content\Page', 'layout_template_id');
	}

	/**
	 * The number of pages that belong to the layout template.
	 *
	 * @return integer
	 */
	public function getNumberOfPages()
	{
		return $this->pages()->count();
	}

	/**
	 * The blog articles that belong to the layout template.
	 *
	 * @return Collection
	 */
	public function articles()
	{
		return $this->hasMany('Regulus\Fractal\Models\Blog\Article', 'layout_template_id');
	}

	/**
	 * The number of blog articles that belong to the layout template.
	 *
	 * @return integer
	 */
	public function getNumberOfArticles()
	{
		return $this->articles()->count();
	}

	/**
	 * Get the last updated date/time.
	 *
	 * @param  mixed    $dateFormat
	 * @return string
	 */
	public function getLastUpdatedDateTime($dateFormat = false)
	{
		if (!$dateFormat)
			$dateFormat = config('format.defaults.datetime');

		return $this->updated_at != "0000-00-00" ? date($dateFormat, strtotime($this->updated_at)) : date($dateFormat, strtotime($this->created_at));
	}

	/**
	 * Get layout template search results.
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

		Fractal::setRequestedPage();

		return $categories->paginate($searchData['itemsPerPage']);
	}

}