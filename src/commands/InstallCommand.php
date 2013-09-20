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
		$this->output->writeln('');
		$this->comment('Installing Fractal...');
		$this->info('---------------------');
		$this->output->writeln('');

		//run database migrations
		$migrationPackages = array(
			'regulus/fractal',
			'regulus/activity-log',
		);
		foreach ($migrationPackages as $migrationPackage) {
			$this->output->writeln('<info>Migrating DB tables:</info> '.$migrationPackage);
			$this->call('migrate', array('--env' => $this->option('env'), '--package' => $migrationPackage));
		}

		//seed database tables
		$seedTables = array(
			'Settings',
			'Menus',
			'MenuItems',
			'Pages',
		);
		foreach ($seedTables as $seedTable) {
			$this->output->writeln('<info>Seeding DB table:</info> '.$seedTable);
			$this->call('db:seed', array('--class' => $seedTable.'TableSeeder'));
		}

		//publish config files for Fractal and its required packages
		$configPackages = array(
			'regulus/fractal',
			'regulus/solid-site',
			'aquanode/formation',
		);
		foreach ($configPackages as $configPackage) {
			$this->output->writeln('<info>Publishing config:</info> '.$configPackage);
			$this->call('config:publish', array('--env' => $this->option('env'), 'package' => $configPackage, '--path' => 'vendor/'.$configPackage.'/src/config'));
		}

		//publish assets for Fractal and its required packages
		$this->info('Publishing assets');
		if (Config::get('fractal::workbench')) {
			$arguments = array('--bench'   => 'regulus/fractal');
		} else {
			$arguments = array('package' => 'regulus/fractal');
		}
		$this->call('asset:publish', $arguments);

		$this->output->writeln('');
		$this->info('------------------');
		$this->comment('Fractal installed!');
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