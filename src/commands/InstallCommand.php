<?php namespace Regulus\Fractal\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Illuminate\Support\Facades\Config;

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
		$divider = '----------------------';

		$this->output->writeln('');
		$this->info($divider);
		$this->comment('Installing Fractal...');
		$this->info($divider);
		$this->output->writeln('');

		$workbench = Config::get('fractal::workbench');

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

			if ($workbench) {
				$prefix = 'workbench';
			} else {
				$prefix = 'vendor';
			}

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
			'MenuItems',
			'ContentLayoutTemplates',
			'ContentPages',
			'ContentAreas',
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
			'regulus/fractal',
			'regulus/solid-site',
			'aquanode/formation',
		);
		foreach ($configPackages as $key => $configPackage) {
			if ($key)
				$this->output->writeln('');

			$this->output->writeln('<info>Publishing configuration:</info> '.$configPackage);
			$this->call('config:publish', array(
				'package' => $configPackage,
				'--env'   => $this->option('env'),
				'--path'  => 'vendor/'.$configPackage.'/src/config'
			));
		}

		$this->output->writeln('');

		//publish assets for Fractal and its required packages
		$this->info($divider);
		$this->comment('Publishing assets...');
		$this->info($divider);

		if ($workbench) {
			$arguments = array('--bench' => 'regulus/fractal');
		} else {
			$arguments = array('package' => 'regulus/fractal');
		}
		$this->call('asset:publish', $arguments);

		$this->output->writeln('');
		$this->info($divider);
		$this->comment('Fractal installed!');
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