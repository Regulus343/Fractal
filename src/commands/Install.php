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

		//install Identify
		$this->call('identify:install', [
			'--env' => $this->option('env'),
		]);

		//run database migrations
		$this->comment('Migrating DB tables...');
		$this->info($divider);

		$this->call('migrate', [
			'--env'  => $this->option('env'),
			'--path' => 'vendor/regulus/fractal/vendor/regulus/activity-log/src/migrations',
		]);

		$this->call('migrate', [
			'--env'  => $this->option('env'),
			'--path' => 'vendor/regulus/fractal/src/migrations',
		]);

		//seed database tables
		$this->output->writeln('');
		$this->comment('Seeding DB tables...');
		$this->info($divider);

		$this->call('db:seed', ['--class' => 'FractalSeeder']);

		//export default settings
		$this->output->writeln('');
		$this->comment('Exporting Fractal\'s default settings from database to config file...');
		Fractal::exportSettings(null, true);
		$this->info('Fractal settings exported');

		//export default menus
		$this->output->writeln('');
		$this->comment('Exporting Fractal\'s default menus from database to config file...');
		Fractal::exportMenus(true);
		$this->info('Fractal menus exported');

		//swap out default LoadConfiguration class
		$bootstrapAppFile = base_path('bootstrap/app.php');
		if (is_file($bootstrapAppFile) && isset($xx))
		{
			$bootstrapAppContents       = file_get_contents($bootstrapAppFile);
			$bootstrapAppContentsLength = strlen($bootstrapAppContents);

			$singleton = "\n".'$app'."->singleton(\n\t'Illuminate\Foundation\Bootstrap\LoadConfiguration',\n\t'Regulus\Fractal\Libraries\LoadConfiguration'\n);\n";

			//ensure singleton has not already been added to file
			if (strpos($bootstrapAppContents, $singleton) === false)
			{
				$this->output->writeln('');

				$singletonAddBefore = "\n/*\n|--------------------------------------------------------------------------\n| Return The Application";

				$bootstrapAppContents = str_replace($singletonAddBefore, $singleton.$singletonAddBefore, $bootstrapAppContents);

				//ensure update was successful before saving file
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

		//show installed text
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