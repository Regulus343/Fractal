<?php namespace Regulus\Fractal\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Regulus\Fractal\Facade as Fractal;

class Install extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'fractal:install';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Fractal\'s install command.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		$divider = '----------------------';

		$this->output->writeln('');
		$this->info($divider);
		$this->comment('Installing Fractal...');
		$this->info($divider);

		// install Identify
		$this->call('identify:install', [
			'--env' => $this->option('env'),
		]);

		// run database migrations
		$this->comment('Migrating DB tables...');
		$this->info($divider);

		$this->call('migrate', [
			'--env'  => $this->option('env'),
			'--path' => 'vendor/vendor/regulus/activity-log/src/migrations',
		]);

		$this->call('migrate', [
			'--env'  => $this->option('env'),
			'--path' => 'vendor/regulus/fractal/src/migrations',
		]);

		// seed database tables
		$this->output->writeln('');
		$this->comment('Seeding DB tables...');
		$this->info($divider);

		$this->call('db:seed', ['--class' => 'FractalSeeder']);

		// export default settings
		$this->output->writeln('');
		$this->comment('Exporting Fractal\'s default settings from database to config file...');
		Fractal::exportSettings(null, true);
		$this->info('Fractal settings exported');

		// export default menus
		$this->output->writeln('');
		$this->comment('Exporting Fractal\'s default menus from database to config file...');
		Fractal::exportMenus(true);
		$this->info('Fractal menus exported');

		// swap out default LoadConfiguration class
		$bootstrapAppFile = base_path('bootstrap/app.php');
		if (is_file($bootstrapAppFile))
		{
			$bootstrapAppContents       = file_get_contents($bootstrapAppFile);
			$bootstrapAppContentsLength = strlen($bootstrapAppContents);

			$singleton = "\n".'$app'."->singleton(\n\t'Illuminate\Foundation\Bootstrap\LoadConfiguration',\n\t'Regulus\Fractal\Libraries\LoadConfiguration'\n);\n";

			// ensure singleton has not already been added to file
			if (strpos($bootstrapAppContents, $singleton) === false)
			{
				$this->output->writeln('');

				$singletonAddBefore = "\n/*\n|--------------------------------------------------------------------------\n| Return The Application";

				$bootstrapAppContents = str_replace($singletonAddBefore, $singleton.$singletonAddBefore, $bootstrapAppContents);

				// ensure update was successful before saving file
				if (strlen($bootstrapAppContents) > $bootstrapAppContentsLength)
				{
					file_put_contents($bootstrapAppFile, $bootstrapAppContents);

					$this->output->writeln('<info>Added LoadConfiguration singleton to </info>bootstrap/app.php');
				} else {
					$this->output->writeln('<info>Was not able to swap out LoadConfiguration singleton in </info>bootstrap/app.php<info>. Refer to readme to add it manually.</info>');
				}
			}
		} else {
			$this->output->writeln("\n".'<info>Could not find </info>bootstrap/app.php<info>. Was not able to swap out LoadConfiguration singleton. Refer to readme to add it manually.</info>');
		}

		// add content type URIs and model classes to Activity Log's "log" config file
		$logConfigFile = config_path('log.php');
		if (is_file($logConfigFile))
		{
			$logConfigContents       = file_get_contents($logConfigFile);
			$logConfigContentsLength = strlen($logConfigContents);

			$contentTypes  = "'menu' => [\n\t\t\t'uri'   => 'admin/menus/:id/edit',\n\t\t\t'model' => 'Regulus\Fractal\Models\Content\Menu',\n\t\t],\n\n\t\t";
			$contentTypes .= "'page' => [\n\t\t\t'uri'   => 'admin/pages/:id/edit',\n\t\t\t'model' => 'Regulus\Fractal\Models\Content\Page',\n\t\t],\n\n\t\t";
			$contentTypes .= "'file' => [\n\t\t\t'uri'   => 'admin/files/:id/edit',\n\t\t\t'model' => 'Regulus\Fractal\Models\Content\File',\n\t\t],\n\n\t\t";
			$contentTypes .= "'layout_template' => [\n\t\t\t'uri'   => 'admin/layout-templates/:id/edit',\n\t\t\t'model' => 'Regulus\Fractal\Models\Content\LayoutTemplate',\n\t\t],\n\n\t\t";

			$contentTypes .= "'item' => [\n\t\t\t'uri'   => 'admin/media/items/:id/edit',\n\t\t\t'model' => 'Regulus\Fractal\Models\Media\Item',\n\t\t],\n\n\t\t";
			$contentTypes .= "'type' => [\n\t\t\t'uri'   => 'admin/media/types/:id/edit',\n\t\t\t'model' => 'Regulus\Fractal\Models\Media\Type',\n\t\t],\n\n\t\t";
			$contentTypes .= "'set' => [\n\t\t\t'uri'   => 'admin/media/sets/:id/edit',\n\t\t\t'model' => 'Regulus\Fractal\Models\Media\Set',\n\t\t],\n\n\t\t";

			$contentTypes .= "'article' => [\n\t\t\t'uri'   => 'admin/blogs/articles/:id/edit',\n\t\t\t'model' => 'Regulus\Fractal\Models\Blog\Article',\n\t\t],\n\n\t\t";
			$contentTypes .= "'category' => [\n\t\t\t'uri'   => 'admin/blogs/categories/:id/edit',\n\t\t\t'model' => 'Regulus\Fractal\Models\Blog\Category',\n\t\t],\n\n\t\t";

			$contentTypes .= "'user' => [\n\t\t\t'uri'   => 'admin/users/:id/edit',\n\t\t\t'model' => 'Regulus\Fractal\Models\User\User',\n\t\t],\n\n\t\t";
			$contentTypes .= "'role' => [\n\t\t\t'uri'   => 'admin/users/roles/:id/edit',\n\t\t\t'model' => 'Regulus\Fractal\Models\User\Role',\n\t\t],\n\n\t\t";
			$contentTypes .= "'permission' => [\n\t\t\t'uri'   => 'admin/users/permissions/:id/edit',\n\t\t\t'model' => 'Regulus\Fractal\Models\User\Permission',\n\t\t],\n\n\t\t";

			$contentTypes .= "'setting' => [\n\t\t\t'uri'   => 'admin/settings',\n\t\t\t'model' => 'Regulus\Fractal\Models\General\Setting',\n\t\t],";

			// ensure example content type has not already been replaced
			if (strpos($logConfigContents, $contentTypes) === false)
			{
				$this->output->writeln('');

				$exampleContentType = "/* 'item' => [\n\t\t\t'uri'       => 'view/:id',\n\t\t\t'subdomain' => 'items',\n\t\t\t'model'     => 'App\Models\Item',\n\t\t], */";

				$logConfigContents = str_replace($exampleContentType, $contentTypes, $logConfigContents);

				// ensure update was successful before saving file
				if (strlen($logConfigContents) > $logConfigContentsLength)
				{
					file_put_contents($logConfigFile, $logConfigContents);

					$this->output->writeln('<info>Added content types to Activity Log\'s </info>config/log.php<info> config file</info>');
				} else {
					$this->output->writeln('<info>Was not able to add content types to Activity Log\'s </info>config/log.php<info> config file</info>');
				}
			}
		} else {
			$this->output->writeln("\n".'<info>Could not find Activity Log\'s </info>config/log.php<info> config file. Was not able populate \'</info>content_types<info>\' array.</info>');
		}

		// show installed text
		$this->output->writeln('');
		$this->info($divider);
		$this->comment('Fractal installed!');
		$this->output->writeln('');
		$this->output->writeln('<info>Log in with "</info>admin<info>" / "</info>password<info>": </info>'.Fractal::url());
		$this->info($divider);
		$this->output->writeln('');
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [];
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [
			[
				'userstable',
				'u',
				InputOption::VALUE_OPTIONAL,
				'The name of the users table (from which the other auth table names are derived).',
				'auth_users',
			],
		];
	}

}