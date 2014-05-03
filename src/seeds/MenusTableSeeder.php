<?php

class MenusTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		DB::table('menus')->truncate();

		$timestamp = date('Y-m-d H:i:s');

		$menus = array(
			array(
				'name'       => 'CMS Main',
				'cms'        => true,
				'created_at' => $timestamp,
				'updated_at' => $timestamp,
			),
			array(
				'name'       => 'CMS Account',
				'cms'        => true,
				'created_at' => $timestamp,
				'updated_at' => $timestamp,
			),
			array(
				'name'       => 'Main',
				'created_at' => $timestamp,
				'updated_at' => $timestamp,
			),
			array(
				'name'       => 'Footer',
				'created_at' => $timestamp,
				'updated_at' => $timestamp,
			),
		);

		foreach ($menus as $menu) {
			DB::table('menus')->insert($menu);
		}
	}

}