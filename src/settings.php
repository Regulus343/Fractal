<?php namespace Regulus\Fractal;

/*
|--------------------------------------------------------------------------
| Settings
|--------------------------------------------------------------------------
|
| Settings from "settings" table.
|
*/

use Regulus\SolidSite\SolidSite as Site;

$websiteName = Setting::where('name', '=', 'Website Name')->first();
if (!empty($websiteName)) Site::set('name', $websiteName->value);

$webmasterEmail = Setting::where('name', '=', 'Webmaster Email')->first();
if (!empty($webmasterEmail)) Site::set('email', $webmasterEmail->value);