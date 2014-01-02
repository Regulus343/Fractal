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

- [Composer Package Installation](#composer-package-installation)
- [Installation: Command Line](#command-line-installation)
- [Installation: Manual](#manual-installation)
- [Installation: Authentication](#auth-installation)
- [First Log In](#first-log-in)
- [Basic Usage](#basic-usage)

<a name="composer-package-installation"></a>
## Composer Package Installation

**Basic installation, service provider registration, and aliasing:**

To install Fractal, make sure "regulus/fractal" has been added to Laravel 4's `composer.json` file.

	"require": {
		"regulus/fractal": "dev-master"
	},

Then run `php composer.phar update` from the command line. Composer will install the Fractal package.

<a name="command-line-installation"></a>
## Command Line Installation

**Register service provider and set up alias:**

Now, all you have to do is register the service provider, set up Fractal's alias in `app/config/app.php`, publish the assets, and run the install command. Add this to the `providers` array:

	'Regulus\Fractal\FractalServiceProvider',

And add this to the `aliases` array:

	'CMS' => 'Regulus\Fractal\Fractal',

**Run the install command:**

	php artisan fractal:install

Fractal should now be installed.

You may now skip ahead to the [Composer Package Installation for Authentication](#auth-composer-package-installation) section.

<a name="manual-installation"></a>
## Manual Installation

**Publishing config file:**

If you wish to customize the configuration of Fractal, you will need to publish the config file. Run this from the command line:

	php artisan config:publish regulus/fractal

You will now be able to edit the config files in `app/config/packages/regulus/fractal`. Since Fractal makes use of a lot of SolidSite features, you may wish to publish SolidSite's config file too:

	php artisan config:publish regulus/solid-site

**Run the migrations and seed the database:**

To run the migrations, run the following from the command line:

	php artisan migrate --package=regulus/fractal

	php artisan migrate --package=regulus/activity-log

> **Note:** It is important that you run the migrations to create Fractal's necessary database tables before you register the service provider. If you get the order wrong, you can simply turn the `migrations` variable to `false` in `config.php` and run your migrations. If you do this, remember to turn `migrations` to `true` again after you're done.

To seed the database tables, add the following to the `run()` method in `database/seeds/DatabaseSeeder.php`:

	$this->call('SettingsTableSeeder');
	$this->command->info('Settings table seeded.');

	$this->call('MenusTableSeeder');
	$this->command->info('Menus table seeded.');

	$this->call('MenuItemsTableSeeder');
	$this->command->info('Menu Items table seeded.');

	$this->call('PagesTableSeeder');
	$this->command->info('Pages table seeded.');

...And then running `php artisan db:seed` from the command line.

**Publish assets:**

To publish Fractal's assets (CSS, JS, and client-side plugins), run the following from the command line:

	php artisan asset:publish regulus/fractal

**Register service provider and set up alias:**

Now, all you have to do is register the service provider, set up Fractal's alias in `app/config/app.php`, and publish the assets. Add this to the `providers` array:

	'Regulus\Fractal\FractalServiceProvider',

And add this to the `aliases` array:

	'CMS' => 'Regulus\Fractal\Fractal',

You may use 'Fractal', or another alias, but 'CMS' is recommended for the sake of simplicity.

<a name="auth-installation"></a>
## Installation for Authentication

**Install Composer package:**

To install Identify, make sure "regulus/identify" has been added to Laravel 4's `composer.json` file.

	"require": {
		"regulus/identify": "dev-master"
	},

**Register service provider and set up alias:**

Now, all you have to do is register the service provider, set up Identify's alias in `app/config/app.php`, set 'model' to `Regulus\Identify\User` in `app/config/auth.php`, and run the install command. Add this to the `providers` array:

	'Regulus\Identify\IdentifyServiceProvider',

And add this to the `aliases` array:

	'Auth' => 'Regulus\Identify\Identify',

You may use 'Identify', or another alias, but 'Auth' is recommended for the sake of simplicity.

Next, change the `model` variable in `app/config/auth.php` to `Regulus\Identify\User`.

**Run the install command:**

	php artisan identify:install

Identify should now be installed.

You should now have 4 users, 'Admin', 'TestUser', 'TestUser2', and 'TestUser3'. All of the passwords are 'password' and the usernames are case insensitive, so you may simply type 'admin' and 'password' to log in. The 3 initial roles are 'Administrator', 'Moderator', and 'Member'. 'Admin' has the 'Administrator' role, 'TestUser' has the 'Moderator' role, the final 2 users have the 'Member' role.

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

To set a `developer` session variable to `true`, go to `website.com/admin/developer`. This will identify you as the web developer for Fractal and you may be able to see more information and manage extra settings.

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