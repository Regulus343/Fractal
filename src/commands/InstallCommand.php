<?php namespace Regulus\Fractal\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Illuminate\Support\Facades\Config;

use Regulus\Fractal\Facade as Fractal;

class InstallCommand extends Command {

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
		$workbench = Config::get('fractal::workbench');

		$divider = '----------------------';

		$this->output->writeln('');
		$this->info($divider);
		$this->comment('Installing Fractal...');
		$this->info($divider);
		$this->output->writeln('');

		//install Identify
		$this->call('identify:install');

		//run database migrations
		$this->comment('Migrating DB tables...');
		$this->info($divider);

		$migrationPackages = array(
			'regulus/fractal',
			'regulus/activity-log',
		);
		foreach ($migrationPackages as $key => $migrationPackage) {
			if ($key)
				$this->output->writeln('');

			if ($workbench)
				$prefix = 'workbench';
			else
				$prefix = 'vendor';

			$this->output->writeln('<info>Migrating DB tables:</info> '.$migrationPackage);
			$this->call('migrate', array(
				'--env'     => $this->option('env'),
				'--package' => $migrationPackage,
				'--path'    => $prefix.'/'.$migrationPackage.'/src/migrations'
			));
		}

		$this->output->writeln('');

		//seed database tables
		$this->comment('Seeding DB tables...');
		$this->info($divider);

		$seedTables = array(
			'Settings',
			'Menus',
			'ContentLayoutTemplates',
			'ContentPages',
			'ContentAreas',
			'FileMediaTypes',
		);
		foreach ($seedTables as $seedTable) {
			$this->output->writeln('<info>Seeding DB table:</info> '.$seedTable);
			$this->call('db:seed', array('--class' => $seedTable.'TableSeeder'));
		}

		$this->output->writeln('');

		//publish config files for Fractal and its required packages
		$this->comment('Publishing configuration...');
		$this->info($divider);

		$configPackages = array(
			'regulus/solid-site',
			'regulus/tetra-text',
			'aquanode/formation',
			'aquanode/elemental',
			'aquanode/upstream',
		);

		if (!$workbench)
			$configPackages = array_merge(array('regulus/fractal'), $configPackages); 

		foreach ($configPackages as $key => $configPackage) {
			if ($key)
				$this->output->writeln('');

			$configPath = $configPackage;

			if ($configPackage != "regulus/fractal")
				$configPath = "regulus/fractal/vendor/".$configPath;

			$this->output->writeln('<info>Publishing configuration:</info> '.$configPackage);
			$this->call('config:publish', array(
				'package' => $configPackage,
				'--env'   => $this->option('env'),
				'--path'  => $prefix.'/'.$configPath.'/src/config'
			));
		}

		$this->output->writeln('');

		//export default settings
		$this->comment('Exporting Fractal\'s default settings from database to config file...');
		Fractal::exportSettings(null, true);
		$this->info('Fractal settings exported');
		$this->output->writeln('');

		//export default menus
		$this->comment('Exporting Fractal\'s default menus from database to config file...');
		Fractal::exportMenus(true);
		$this->info('Fractal menus exported');
		$this->output->writeln('');

		//publish assets for Fractal and its required packages
		$this->info($divider);
		$this->comment('Publishing assets...');
		$this->info($divider);

		if ($workbench)
			$arguments = array('--bench' => 'regulus/fractal');
		else
			$arguments = array('package' => 'regulus/fractal');

		$this->call('asset:publish', $arguments);

		$arguments['package'] = "aquanode/formation";

		if ($workbench)
			$arguments['--bench'] = "regulus/fractal/vendor/aquanode/formation";

		$this->call('asset:publish', $arguments);

		$this->output->writeln('');
		$this->info($divider);
		$this->comment('Fractal installed!');
		$this->output->writeln('');
		$this->info('Log in with "admin" / "password": '.Fractal::url());
		$this->info($divider);
		$this->output->writeln('');
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	/*protected function getArguments()
	{
		return array(
			array('example', InputArgument::REQUIRED, 'An example argument.'),
		);
	}*/

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	/*protected function getOptions()
	{
		return array(
			array('example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null),
		);
	}*/

}