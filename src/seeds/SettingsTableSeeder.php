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
				'value'      => 'Website Name',
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
				'rules'      => 'required, numeric',
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