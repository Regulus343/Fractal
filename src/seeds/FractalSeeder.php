<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class FractalSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();

		$seeds = [
			'Settings',
			'Menus',
			'ContentLayoutTemplates',
			'ContentPages',
			'ContentAreas',
			'FileMediaTypes',
		];

		foreach ($seeds as $seed)
		{
			$this->call($seed.'TableSeeder');
		}
	}

}