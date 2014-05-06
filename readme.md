Fractal
=======

**A simple, versatile CMS base for Laravel 4 which uses Twitter Bootstrap. [PRE ALPHA; Not Ready for Production Use]**

Fractal is a simple yet versative Admin/CMS base for Laravel 4. It is built to work with Twitter Bootstrap by default and can be completely customized to work how you, the developer, want it to. The core philosophy behind Fractal is:

**Maintain simplicity while ensuring the developer is free to customize and modify as they please.**

Fractal attempts not to lock you into a specific way of doing things whenever possible. You may define controllers additional to the core controllers, remove core controllers, or point the URI paths of core controllers to your own custom controllers. You may adjust the views location for all Fractal view files so you can completely customize the views, or you may simple edit `config/tables.php` to adjust the setup of the content display tables.

You may use the menu to handle all Admin/CMS permissions (Fractal's own menu makes use of its "menu" and "menu_items" database tables for full and easy customization), or you may simply set your permissions in `config/config.php`. Fractal attempts to be authentication class agnostic so you may use the authentication class of your choice (although it does come preconfigured to use the "Identify" authentication package).

Some of the things you can do with Fractal:

- Log in / log out
- Manage account
- Manage users
- Manage website settings
- Manage all menus via database, for both front-end website and admin/CMS area
- Manage content pages for website
- Manage files
- Extra website settings management for developers
- Build Twitter Bootstrap-enabled menu markup for views
- Build an array of a menu which you can build custom markup for
- Easily change base URI which defaults to `website.com/admin`
- Easily add or remove controllers
- Easily use custom views
- Easily build forms (due to use of Formation package)

## Table of Contents

- [Installation](#installation)
- [Installation: Authentication](#auth-installation)
- [First Log In](#first-log-in)
- [Basic Usage](#basic-usage)

<a name="installation"></a>
## Installation

**Composer package installation:**

To install Fractal, make sure "regulus/fractal" has been added to Laravel 4's `composer.json` file.

	"require": {
		"regulus/fractal": "dev-master"
	},

Then run `php composer.phar update` from the command line. Composer will install the Fractal package.

**Register service provider and set up alias:**

Now, all you have to do is register the service provider, set up Fractal's alias in `app/config/app.php`, publish the assets, and run the install command. Add this to the `providers` array:

	'Regulus\Fractal\FractalServiceProvider',

And add this to the `aliases` array:

	'CMS' => 'Regulus\Fractal\Fractal',

You may use 'Fractal', or another alias, but 'CMS' is recommended for the sake of simplicity.

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

Lastly, change the `model` variable in `app/config/auth.php` to `Regulus\Identify\User`.

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

	echo CMS::getMenuMarkup(); //get "Main" menu markup

	echo CMS::getMenuMarkup('Main'); //get "Footer" menu markup

**Getting an array of menu items:**

	$menu = CMS::getMenuArray(); //get "Main" menu array

	$menu = CMS::getMenuArray('Main'); //get "Footer" menu array