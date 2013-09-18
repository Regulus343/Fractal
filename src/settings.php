<?php namespace Regulus\Fractal;

/*
|--------------------------------------------------------------------------
| Settings
|--------------------------------------------------------------------------
|
| Settings from "settings" table.
|
*/

use Illuminate\Support\Facades\Config;

use Regulus\SolidSite\SolidSite as Site;

//ensure DB tables have been migrated first
if (Config::get('fractal::migrated')) {
	$websiteName = Setting::where('name', '=', 'Website Name')->first();
	if (!empty($websiteName)) Site::set('name', $websiteName->value);

	$webmasterEmail = Setting::where('name', '=', 'Webmaster Email')->first();
	if (!empty($webmasterEmail)) Site::set('email', $webmasterEmail->value);
}