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

use \Site;

$websiteName = Fractal::getSetting('Website Name');
if ($websiteName)
	Site::set('name', $websiteName);

$webmasterEmail = Fractal::getSetting('Webmaster Email');
if ($webmasterEmail)
	Site::set('email', $webmasterEmail);