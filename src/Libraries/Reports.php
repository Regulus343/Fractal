<?php namespace Regulus\Fractal\Libraries;

use Illuminate\Support\Facades\DB;

use Fractal;

use Regulus\Fractal\Models\Content\View;

use Regulus\Fractal\Models\Content\Page;
use Regulus\Fractal\Models\Blog\Article;
use Regulus\Fractal\Models\Media\Item;

class Reports {

	/**
	 * Total Views
	 *
	 * @param  string   $range
	 * @return array
	 */
	public static function totalViews($range = 'year')
	{
		if (!in_array($range, ['year', 'month']))
			$range = "year";

		switch ($range)
		{
			case "year":
				$interval         = "month";
				$dateFieldSelect  = DB::raw('left(created_at, 7) as '.$interval);
				$dateFieldGroupBy = DB::raw('left(created_at, 7)');
				$minimumDate      = date('Y-m-d', strtotime('-12 '.$interval.'s'));
				break;

			case "month":
				$interval         = "day";
				$dateFieldSelect  = DB::raw('left(created_at, 10) as '.$interval);
				$dateFieldGroupBy = DB::raw('left(created_at, 10)');
				$minimumDate      = date('Y-m-01');
				break;
		}

		$reportData = View::select([
				$dateFieldSelect,
				'content_type',
				DB::raw('sum(views) as views'),
			])
			->where('created_at', '>=', $minimumDate)
			->groupBy($dateFieldGroupBy)
			->groupBy('content_type')
			->orderBy('created_at')
			->orderBy('content_type')
			->get();

		$results = [
			'labels' => [],
			'values' => [],
		];

		$contentTypes = [];

		$maxDay = 1;

		// get month/day labels
		foreach ($reportData as $item)
		{
			$itemInterval = date(($interval == "month" ? 'F' : 'j'), strtotime($item->{$interval}));

			if ($interval == "month")
			{
				if (!in_array($itemInterval, $results['labels']))
					$results['labels'][] = $itemInterval;
			} else {
				if ($itemInterval > $maxDay)
					$maxDay = $itemInterval;
			}

			$contentTypeLabel = static::getContentTypeLabel($item->content_type, 2);

			if (!in_array($contentTypeLabel, $contentTypes))
				$contentTypes[] = $contentTypeLabel;
		}

		if ($interval == "day")
		{
			for ($d = 1; $d <= $maxDay; $d++)
			{
				$day = ($d == 1 || $d == $maxDay) ? date('M').' '.$d : $d;

				$results['labels'][] = $day;
			}
		}

		// set all months/days to zero for each content type
		foreach ($contentTypes as $contentType)
		{
			for ($i = 0; $i < count($results['labels']); $i++)
			{
				$results['values'][$contentType][$results['labels'][$i]] = 0;
			}
		}

		foreach ($reportData as $item)
		{
			$itemInterval = date(($interval == "month" ? 'F' : 'j'), strtotime($item->{$interval}));

			if ($interval == "day" && ($itemInterval == 1 || $itemInterval == $maxDay))
				$itemInterval = date('M').' '.$itemInterval;

			$results['values'][static::getContentTypeLabel($item->content_type, 2)][$itemInterval] += $item->views;
		}

		return $results;
	}

	/**
	 * Unique Views
	 *
	 * @param  string   $range
	 * @return array
	 */
	public static function uniqueViews($range = 'year')
	{
		if (!in_array($range, ['year', 'month']))
			$range = "year";

		switch ($range)
		{
			case "year":
				$interval         = "month";
				$dateFieldSelect  = DB::raw('left(content_views.created_at, 7) as '.$interval);
				$dateFieldGroupBy = DB::raw('left(created_at, 7)');
				$minimumDate      = date('Y-m-d', strtotime('-12 '.$interval.'s'));
				break;

			case "month":
				$interval         = "day";
				$dateFieldSelect  = DB::raw('left(content_views.created_at, 10) as '.$interval);
				$dateFieldGroupBy = DB::raw('left(created_at, 10)');
				$minimumDate      = date('Y-m-01');
				break;
		}

		$reportDataSub = View::select([
				'id',
				'content_type',
				'content_id',
				DB::raw('count(id) as views'),
				'created_at',
			])
			->where('created_at', '>=', DB::raw('\''.$minimumDate.'\''))
			->groupBy($dateFieldGroupBy)
			->groupBy('content_type')
			->groupBy('content_id')
			->orderBy('content_type');

		$reportData = View::select([
				$dateFieldSelect,
				'content_views.content_type',
				'content_views_sub.views',
			])
			->join(DB::raw('('.$reportDataSub->toSql().') as content_views_sub'), 'content_views.id', '=', 'content_views_sub.id')
			->orderBy('content_views.created_at')
			->orderBy('content_views.content_type')
			->get();

		$results = [
			'labels' => [],
			'values' => [],
		];

		$contentTypes = [];

		$maxDay = 1;

		// get month/day labels
		foreach ($reportData as $item)
		{
			$itemInterval = date(($interval == "month" ? 'F' : 'j'), strtotime($item->{$interval}));

			if ($interval == "month")
			{
				if (!in_array($itemInterval, $results['labels']))
					$results['labels'][] = $itemInterval;
			} else {
				if ($itemInterval > $maxDay)
					$maxDay = $itemInterval;
			}

			$contentTypeLabel = static::getContentTypeLabel($item->content_type, 2);

			if (!in_array($contentTypeLabel, $contentTypes))
				$contentTypes[] = $contentTypeLabel;
		}

		if ($interval == "day")
		{
			for ($d = 1; $d <= $maxDay; $d++)
			{
				$day = ($d == 1 || $d == $maxDay) ? date('M').' '.$d : $d;

				$results['labels'][] = $day;
			}
		}

		// set all months/days to zero for each content type
		foreach ($contentTypes as $contentType)
		{
			for ($i = 0; $i < count($results['labels']); $i++)
			{
				$results['values'][$contentType][$results['labels'][$i]] = 0;
			}
		}

		foreach ($reportData as $item)
		{
			$itemInterval = date(($interval == "month" ? 'F' : 'j'), strtotime($item->{$interval}));

			if ($interval == "day" && ($itemInterval == 1 || $itemInterval == $maxDay))
				$itemInterval = date('M').' '.$itemInterval;

			$results['values'][static::getContentTypeLabel($item->content_type, 2)][$itemInterval] ++;
		}

		return $results;
	}

	/**
	 * Popular Content
	 *
	 * @param  string   $range
	 * @return array
	 */
	public static function popularContent($range = 'year', $contentTypes = null)
	{
		if (!in_array($range, ['year', 'month']))
			$range = "year";

		switch ($range)
		{
			case "year":
				$interval         = "month";
				$dateFieldSelect  = DB::raw('left(content_views.created_at, 7) as '.$interval);
				$dateFieldGroupBy = DB::raw('left(content_views.created_at, 7)');
				$minimumDate      = date('Y-m-d', strtotime('-12 '.$interval.'s'));
				break;

			case "month":
				$interval         = "day";
				$dateFieldSelect  = DB::raw('left(content_views.created_at, 10) as '.$interval);
				$dateFieldGroupBy = DB::raw('left(content_views.created_at, 10)');
				$minimumDate      = date('Y-m-01');
				break;
		}

		if (is_null($contentTypes))
			$contentTypes = ['Page', 'Article', 'Item'];

		if (is_string($contentTypes))
			$contentTypes = [$contentTypes];

		$reportDataContentItems = View::select([
				'id',
				'content_type',
				'content_id',
			])
			->where('created_at', '>=', DB::raw('\''.$minimumDate.'\''))
			->whereIn('content_type', $contentTypes)
			->groupBy('content_type')
			->groupBy('content_id')
			->orderBy(DB::raw('sum(views)'), 'desc')
			->take(5)
			->get();

		$contentItems = [];
		foreach ($reportDataContentItems as $contentItem)
		{
			$contentItems[] = $contentItem->content_type.'-'.$contentItem->content_id;
		}

		$reportData = View::select([
				'content_views.id',
				$dateFieldSelect,
				'content_views.content_type',
				'content_views.content_id',
				DB::raw('sum(content_views.views) as views'),
				'content_views.created_at',
			])
			->whereIn(DB::raw('concat(content_views.content_type, \'-\', content_views.content_id)'), $contentItems)
			->where('created_at', '>=', $minimumDate)
			->groupBy('content_type')
			->groupBy('content_id')
			->groupBy($dateFieldGroupBy)
			->orderBy('content_views.created_at')
			->orderBy('content_views.content_type')
			->get();

		$results = [
			'labels' => [],
			'values' => [],
		];

		$itemTitles = [];

		$maxDay = 1;

		// get month/day labels
		foreach ($reportData as $i => $item)
		{
			$itemInterval = date(($interval == "month" ? 'F' : 'j'), strtotime($item->{$interval}));

			if ($interval == "month")
			{
				if (!in_array($itemInterval, $results['labels']))
					$results['labels'][] = $itemInterval;
			} else {
				if ($itemInterval > $maxDay)
					$maxDay = $itemInterval;
			}

			$contentItem = null;

			switch ($item->content_type)
			{
				case "Page":    $contentItem = Page::find($item->content_id);    break;
				case "Article": $contentItem = Article::find($item->content_id); break;
				case "Item":    $contentItem = Item::find($item->content_id);    break;
			}

			if (!empty($contentItem))
			{
				$reportData[$i]->content = $contentItem;

				$itemTitle = str_replace("'", "\'", $contentItem->getTitle());

				if (count($contentTypes) > 1)
					$itemTitle = static::getContentTypeLabel($item->content_type).': '.$itemTitle;

				$itemTitle = '<a href="'.$item->content->getUrl().'" target="_blank">'.$itemTitle.'</a>';

				if (!in_array($itemTitle, $itemTitles))
					$itemTitles[] = $itemTitle;
			}
		}

		if ($interval == "day")
		{
			for ($d = 1; $d <= $maxDay; $d++)
			{
				$day = ($d == 1 || $d == $maxDay) ? date('M').' '.$d : $d;

				$results['labels'][] = $day;
			}
		}

		// set all months/days to zero for each content type
		foreach ($itemTitles as $itemTitle)
		{
			for ($i = 0; $i < count($results['labels']); $i++)
			{
				$results['values'][$itemTitle][$results['labels'][$i]] = 0;
			}
		}

		foreach ($reportData as $item)
		{
			$itemInterval = date(($interval == "month" ? 'F' : 'j'), strtotime($item->{$interval}));

			if ($interval == "day" && ($itemInterval == 1 || $itemInterval == $maxDay))
				$itemInterval = date('M').' '.$itemInterval;

			if (isset($item->content) && !empty($item->content))
			{
				$itemTitle = str_replace("'", "\'", $item->content->getTitle());

				if (count($contentTypes) > 1)
					$itemTitle = static::getContentTypeLabel($item->content_type).': '.$itemTitle;

				$itemTitle = '<a href="'.$item->content->getUrl().'" target="_blank">'.$itemTitle.'</a>';

				$results['values'][$itemTitle][$itemInterval] += $item->views;
			}
		}

		return $results;
	}

	/**
	 * Get a content type label.
	 *
	 * @param  string   $contentType
	 * @param  integer  $number
	 * @return string
	 */
	public static function getContentTypeLabel($contentType, $number = 1)
	{
		return Fractal::transChoice('labels.content_types.'.snake_case($contentType), $number);
	}

}