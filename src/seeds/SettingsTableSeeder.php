<?php

class SettingsTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		DB::table('settings')->truncate();

		$timestamp = date('Y-m-d H:i:s');
		$settings = array(
			array(
				'name'       => 'Website Name',
				'value'      => 'Fractal',
				'type'       => 'Text',
				'rules'      => 'required',
				'created_at' => $timestamp,
				'updated_at' => $timestamp,
			),
			array(
				'name'       => 'Webmaster Email',
				'value'      => 'admin@localhost',
				'type'       => 'Text',
				'rules'      => 'required, email',
				'created_at' => $timestamp,
				'updated_at' => $timestamp,
			),
			array(
				'name'       => 'Minimum Password Length',
				'value'      => 8,
				'type'       => 'Text',
				'category'   => 'User',
				'rules'      => 'required, numeric',
				'developer'  => true,
				'created_at' => $timestamp,
				'updated_at' => $timestamp,
			),
			array(
				'name'       => 'Require Unique Email Addresses',
				'value'      => 1,
				'type'       => 'Boolean',
				'category'   => 'User',
				'rules'      => '',
				'developer'  => true,
				'created_at' => $timestamp,
				'updated_at' => $timestamp,
			),
		);

		foreach ($settings as $setting) {
			DB::table('settings')->insert($setting);
		}
	}

}