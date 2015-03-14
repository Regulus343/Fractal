<?php namespace Regulus\Fractal\Libraries;

use Illuminate\Support\Facades\DB;

use Fractal;

use Regulus\Fractal\Models\Content\View;

use Regulus\Fractal\Models\Content\Page;
use Regulus\Fractal\Models\Blogs\Article;
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

		//get month/day labels
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

			$contentTypeLabel = Fractal::trans('labels.contentTypes.plural.'.$item->content_type);

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

		//set all months/days to zero for each content type
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

			$results['values'][Fractal::trans('labels.contentTypes.plural.'.$item->content_type)][$itemInterval] += $item->views;
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

		//get month/day labels
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

			$contentTypeLabel = Fractal::trans('labels.contentTypes.plural.'.$item->content_type);

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

		//set all months/days to zero for each content type
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

			$results['values'][Fractal::trans('labels.contentTypes.plural.'.$item->content_type)][$itemInterval] ++;
		}

		return $results;
	}

	/**
	 * Popular Content
	 *
	 * @param  string   $range
	 * @return array
	 */
	public static function popularContent($range = 'year')
	{
		if (!in_array($range, ['year', 'month']))
			$range = "year";

		switch ($range)
		{
			case "year":
				$interval         = "month";
				$dateFieldSelect  = DB::raw('left(content_views_sub.created_at, 7) as '.$interval);
				$dateFieldGroupBy = DB::raw('left(created_at, 7)');
				$minimumDate      = date('Y-m-d', strtotime('-12 '.$interval.'s'));
				break;

			case "month":
				$interval         = "day";
				$dateFieldSelect  = DB::raw('left(content_views_sub.created_at, 10) as '.$interval);
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
			->orderBy(DB::raw('sum(views)'), 'desc')
			->take(5);

		$reportData = View::select([
				'content_views.id',
				$dateFieldSelect,
				'content_views.content_type',
				'content_views.content_id',
				'content_views_sub.views',
			])
			->join(DB::raw('('.$reportDataSub->toSql().') as content_views_sub'), 'content_views.id', '=', 'content_views_sub.id')
			->orderBy('content_views_sub.created_at')
			->orderBy('content_views.content_type')
			->get();

		$results = [
			'labels' => [],
			'values' => [],
		];

		$contentTypes = ['Page', 'Article', 'Item'];
		$itemTitles   = [];

		$maxDay = 1;

		//get month/day labels
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

			if (in_array($item->content_type, $contentTypes))
			{
				switch ($item->content_type)
				{
					case "Page":    $contentItem = Page::find($item->content_id);    break;
					case "Article": $contentItem = Article::find($item->content_id); break;
					case "Item":    $contentItem = Item::find($item->content_id);    break;
				}

				if (!empty($contentItem))
				{
					$reportData[$i]->content = $contentItem;

					$itemTitle = Fractal::trans('labels.contentTypes.singular.'.$item->content_type).': '.str_replace("'", "\'", $contentItem->getTitle());
					$itemTitle = '<a href="'.$item->content->getUrl().'" target="_blank">'.$itemTitle.'</a>';

					if (!in_array($itemTitle, $itemTitles))
						$itemTitles[] = $itemTitle;
				}
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

		//set all months/days to zero for each content type
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

			$itemTitle = Fractal::trans('labels.contentTypes.singular.'.$item->content_type).': '.str_replace("'", "\'", $item->content->getTitle());
			$itemTitle = '<a href="'.$item->content->getUrl().'" target="_blank">'.$itemTitle.'</a>';

			if (isset($item->content) && !empty($item->content))
				$results['values'][$itemTitle][$itemInterval] += $item->views;
		}

		return $results;
	}

}