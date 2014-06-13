<?php namespace Regulus\Fractal;

use Aquanode\Formation\BaseModel;

use Illuminate\Support\Facades\Request;

use Regulus\Identify\Identify as Auth;

class ContentView extends BaseModel {

	/**
	 * The database table used by the model.
	 *
	 * @var    string
	 */
	protected $table = 'content_views';

	/**
	 * The fillable fields for the model.
	 *
	 * @var    array
	 */
	protected $fillable = array(
		'user_id',
		'content_id',
		'content_type',
		'ip_address',
		'user_agent',
	);

	/**
	 * Log a content view.
	 *
	 * @param  object   $contentItem
	 * @return boolean
	 */
	public static function log($contentItem)
	{
		//require an object with an ID
		if (!is_object($contentItem) || !isset($contentItem->id))
			return false;

		//get content ID and type
		$contentId   = $contentItem->id;
		$contentType = explode('\\', get_class($contentItem));
		$contentType = end($contentType);

		//check to see if logging is turned on
		$logType = Fractal::getSetting('Content View Logging Type');
		if ($logType == "None")
			return false;

		$user      = Auth::user();
		$ipAddress = Request::getClientIp();
		$userAgent = $_SERVER['HTTP_USER_AGENT'];

		//if log type setting is set to "Unique" and an entry already exists, do not create a new entry
		if ($logType == "Unique") {
			$existingEntry = static::where('content_id', $contentId)
				->where('content_type', $contentType)
				->where('ip_address', $ipAddress)
				->first();

			if ($existingEntry) {
				$existingEntry->user_id    = isset($user->id) ? $user->id : 0;
				$existingEntry->user_agent = $userAgent;
				$existingEntry->views ++;
				$existingEntry->save();
				return false;
			}
		}

		$view = new static;
		$view->user_id      = isset($user->id) ? $user->id : 0;
		$view->content_id   = $contentId;
		$view->content_type = $contentType;
		$view->ip_address   = $ipAddress;
		$view->user_agent   = $userAgent;
		$view->save();

		return true;
	}

	/**
	 * Get the number of views for a content item.
	 *
	 * @param  object   $contentItem
	 * @return integer
	 */
	public static function getViewsForItem($contentItem)
	{
		//require an object with an ID
		if (!is_object($contentItem) || !isset($contentItem->id))
			return 0;

		//get content ID and type
		$contentId   = $contentItem->id;
		$contentType = explode('\\', get_class($contentItem));
		$contentType = end($contentType);

		$views = static::select(\DB::raw('sum(views) as total'))
			->where('content_id', $contentId)
			->where('content_type', $contentType)
			->first();

		return (int) $views->total;
	}

	/**
	 * The user of the view if one exists.
	 *
	 * @return User
	 */
	public function user()
	{
		return $this->belongsTo(Config::get('auth.model'), 'user_id');
	}

}