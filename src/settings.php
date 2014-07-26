<?php namespace Regulus\Fractal;

use Regulus\Fractal\Facade as Fractal;

/*
|--------------------------------------------------------------------------
| Settings
|--------------------------------------------------------------------------
|
| Settings from "settings" table.
|
*/

use Illuminate\Support\Facades\Config;

use Regulus\SolidSite\Facade as Site;

//ensure DB tables have been migrated first
if (Config::get('fractal::migrated')) {
	$websiteName = Fractal::getSetting('Website Name');
	if ($websiteName)
		Site::set('name', $websiteName);

	$webmasterEmail = Fractal::getSetting('Webmaster Email');
	if ($webmasterEmail)
		Site::set('email', $webmasterEmail);
}