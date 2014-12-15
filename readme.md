Fractal
=======

**A simple, versatile CMS base for Laravel.**

[![Latest Stable Version](https://poser.pugx.org/regulus/fractal/v/stable.svg)](https://packagist.org/packages/regulus/fractal) [![License](https://poser.pugx.org/regulus/fractal/license.svg)](https://packagist.org/packages/regulus/fractal)

Fractal is a simple yet versative Admin/CMS base for Laravel 4. The core philosophy behind Fractal is:

**Maintain simplicity while ensuring the developer is free to customize and modify as they please.**

Fractal attempts not to lock you into a specific way of doing things whenever possible. You may define controllers additional to the core controllers, remove core controllers, or point the URI paths of core controllers to your own custom controllers. You may adjust the views location for all Fractal view files so you can completely customize the views, or you may simple edit `config/tables.php` to adjust the setup of the content display tables.

Fractal uses the "Identify" authorization/authentication package and uses Twitter Bootstrap 3 as its CSS framework.

Some of the things you can do with Fractal:

- Log in / log out
- Manage account
- Manage users
- Manage website settings
- Manage all menus via database, for both front-end website and admin/CMS area
- Manage content pages for website
	- Create as many separate content areas as you like and create content in Markdown or an HTML WYSIWYG editor
	- Use the layout template system to re-use standardized and custom layouts across pages
	- Re-use content areas across multiple pages
- Manage files
	- Upload files
	- Resize images
	- Create thumbnail images
- Manage blog articles
	- Make use of the same versatile content area system as content pages uses
- Manage media items (images, video, audio, and more...)
- Extra website settings management for developers
- Easily change base URI for CMS which defaults to `website.com/admin`
- Easily add or remove controllers
- Easily use custom views
- Easily build forms (due to use of Formation package)

**Please note that Fractal is still considered Alpha software.**

## Table of Contents

- [Installation](#installation)
- [Installation: Authentication](#auth-installation)
- [First Log In](#first-log-in)
- [Basic Usage](#basic-usage)

<a name="installation"></a>
## Installation

**Install composer package:**

To install Fractal, make sure "regulus/fractal" has been added to Laravel 4's `composer.json` file.

	"require": {
		"regulus/fractal": "dev-master"
	},

Then run `php composer.phar update` from the command line. Composer will install the Fractal package.

**Register service provider and set up alias:**

Now, all you have to do is register the service provider, set up Fractal's alias in `app/config/app.php`, publish the assets, and run the install command. Add this to the `providers` array:

	'Regulus\Fractal\FractalServiceProvider',

And add this to the `aliases` array:

	'Fractal' => 'Regulus\Fractal\Fractal',

**Run the install command:**

	php artisan fractal:install

Fractal will now be installed. This includes all necessary DB migrations, DB seeding, config publishing, and asset publishing.

<a name="auth-installation"></a>
## Installation for Authentication

**Install Composer package:**

To install Identify, make sure "regulus/identify" has been added to Laravel 4's `composer.json` file.

	"require": {
		"regulus/identify": "dev-master"
	},

Then run `php composer.phar update` from the command line. Composer will install the Identify package.

**Register service provider and set up alias:**

Now, all you have to do is register the service provider, set up Identify's alias in `app/config/app.php`, set 'model' to `Regulus\Identify\User` in `app/config/auth.php`, and run the install command. Add this to the `providers` array:

	'Regulus\Identify\IdentifyServiceProvider',

And add this to the `aliases` array:

	'Auth' => 'Regulus\Identify\Identify',

You may use 'Identify', or another alias, but 'Auth' is recommended for the sake of simplicity.

Next, change the `model` variable in `app/config/auth.php` to `Regulus\Identify\User`.

**Run the install command:**

	php artisan identify:install

Identify will now be installed. This includes all necessary DB migrations, DB seeding, and config publishing.

You should now have 4 users, `Admin`, `TestUser`, `TestUser2`, and `TestUser3`. All of the passwords are `password` and the usernames are case insensitive, so you may simply type `admin` and `password` to log in. The 3 initial roles are `Administrator`, `Moderator`, and `Member`. `Admin` has the `Administrator` role, `TestUser` has the `Moderator` role, the final 2 users have the `Member` role.

**Register service provider and set up alias:**

Now, all you have to do is register the service provider, set up Identify's alias in `app/config/app.php`, and set 'model' to `Regulus\Identify\User` in `app/config/auth.php`. Add this to the `providers` array:

	'Regulus\Identify\IdentifyServiceProvider',

And add this to the `aliases` array:

	'Auth' => 'Regulus\Identify\Identify',

You may use 'Identify', or another alias, but 'Auth' is recommended for the sake of simplicity.

Lastly, change the `model` variable in `app/config/auth.php` to `Regulus\Identify\User` and set the `domain` variable in `app/config/session.php` to `.website.com` (using your domain of course) to allow session data to be kept when the user is accessing a subdomain for the blog or media system.

<a name="first-log-in"></a>
## First Log In

**Logging in:**

To log in, go to `website.com/admin` where "website.com" is the name of the site you have installed Fractal on. Type "Admin" and "password" as your username and password and click "Log In".

You should now be logged in to Fractal for the first time. You should now be able to manage the CMS and the settings of the website or web application.

**Enabling Developer Mode:**

To set a `developer` session variable to `true`, go to `website.com/admin/developer`. This will identify you as the web developer for Fractal and you may be able to see more information and manage extra settings. To turn off Developer Mode, go to `website.com/admin/developer/off`.

<a name="basic-usage"></a>
## Basic Usage

**Adjusting Fractal's base URI:**

By default, Fractal's base URI is "admin" making your URLs like `website.com/admin/pages/home/edit`. You may adjust this in the `baseUri` variable in `config.php`.

**Adding additional controllers:**

You may add additional controllers in the `controllers` array in `config.php`. Use `standard` for standard Laravel controllers and `resource` for resource controllers.

**Get Bootstrap-ready menu markup for a view:**

	echo Fractal::getMenuMarkup(); //get "Main" menu markup

	echo Fractal::getMenuMarkup('Footer'); //get "Footer" menu markup

	echo Fractal::getMenuMarkup('Footer', ['class' => 'nav nav-pills']); //set class attribute for menu

**Getting an array of menu items:**

	$menu = Fractal::getMenuArray(); //get "Main" menu array

	$menu = Fractal::getMenuArray('Footer'); //get "Footer" menu array