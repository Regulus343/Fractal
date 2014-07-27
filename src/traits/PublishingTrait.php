<?php namespace Regulus\Fractal\Traits;

trait PublishingTrait {

	/**
	 * Boot the publishing trait for a model.
	 *
	 * @return void
	 */
	public static function bootPublishingTrait()
	{

	}

	/**
	 * Perform the actual publish query on this model instance.
	 *
	 * @return void
	 */
	protected function performPublishOnModel()
	{
		$query = $this->newQuery()->where($this->getKeyName(), $this->getKey());

		$this->{$this->getPublishedAtColumn()} = $time = $this->freshTimestamp();

		$query->update(array($this->getPublishedAtColumn() => $this->fromDateTime($time)));
	}

	/**
	 * Determine if the model instance has been published.
	 *
	 * @return bool
	 */
	public function published()
	{
		return ! is_null($this->{$this->getPublishedAtColumn()});
	}

	/**
	 * Determine if the model instance has been unpublished.
	 *
	 * @return bool
	 */
	public function unpublished()
	{
		return ! $this->published();
	}

	/**
	 * Determine if the model instance is set to be published at a future date.
	 *
	 * @return bool
	 */
	public function publishedFuture()
	{
		return $this->published() && strtotime($this->{$this->getPublishedAtColumn()}) > time();
	}

	/**
	 * Get a new query builder that only includes published items.
	 *
	 * @return \Illuminate\Database\Eloquent\Builder|static
	 */
	public static function onlyPublished()
	{
		$instance = new static;

		$column = $instance->getQualifiedPublishedAtColumn();

		return $instance->newQuery()->whereNotNull($column)->where($column, '<=', date('Y-m-d H:i:s'));
	}

	/**
	 * Get the name of the "published at" column.
	 *
	 * @return string
	 */
	public function getPublishedAtColumn()
	{
		return defined('static::PUBLISHED_AT') ? static::PUBLISHED_AT : 'published_at';
	}

	/**
	 * Get the fully qualified "published at" column.
	 *
	 * @return string
	 */
	public function getQualifiedPublishedAtColumn()
	{
		return $this->getTable().'.'.$this->getPublishedAtColumn();
	}

}