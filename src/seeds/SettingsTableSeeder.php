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

		$defaultDisplayOrder = 100;
		$timestamp           = date('Y-m-d H:i:s');

		$settings = array(
			array(
				'name'          => 'Website Name',
				'value'         => 'Fractal',
				'type'          => 'Text',
				'rules'         => 'required',
				'display_order' => 1,
				'created_at'    => $timestamp,
				'updated_at'    => $timestamp,
			),
			array(
				'name'          => 'Webmaster Email',
				'value'         => 'admin@localhost',
				'type'          => 'Text',
				'rules'         => 'required, email',
				'display_order' => 2,
				'created_at'    => $timestamp,
				'updated_at'    => $timestamp,
			),
			array(
				'name'          => 'Auto Format Titles',
				'value'         => true,
				'type'          => 'Boolean',
				'rules'         => '',
				'developer'     => true,
				'display_order' => $defaultDisplayOrder,
				'created_at'    => $timestamp,
				'updated_at'    => $timestamp,
			),
			array(
				'name'          => 'Items Listed Per Page',
				'value'         => 20,
				'type'          => 'Integer',
				'rules'         => '',
				'display_order' => $defaultDisplayOrder,
				'created_at'    => $timestamp,
				'updated_at'    => $timestamp,
			),
			array(
				'name'          => 'Default Image Thumbnail Size',
				'value'         => 120,
				'type'          => 'Integer',
				'category'      => 'Files',
				'rules'         => '',
				'developer'     => true,
				'display_order' => $defaultDisplayOrder,
				'created_at'    => $timestamp,
				'updated_at'    => $timestamp,
			),
			array(
				'name'          => 'Image Resize Quality',
				'value'         => 60,
				'type'          => 'Integer',
				'category'      => 'Files',
				'rules'         => 'min:1, max:100',
				'developer'     => true,
				'display_order' => $defaultDisplayOrder,
				'created_at'    => $timestamp,
				'updated_at'    => $timestamp,
			),
			array(
				'name'          => 'Minimum Password Length',
				'value'         => 8,
				'type'          => 'Text',
				'category'      => 'Users',
				'rules'         => 'required, numeric',
				'developer'     => true,
				'display_order' => $defaultDisplayOrder,
				'created_at'    => $timestamp,
				'updated_at'    => $timestamp,
			),
			array(
				'name'          => 'Require Unique Email Addresses',
				'value'         => true,
				'type'          => 'Boolean',
				'category'      => 'Users',
				'rules'         => '',
				'developer'     => true,
				'display_order' => $defaultDisplayOrder,
				'created_at'    => $timestamp,
				'updated_at'    => $timestamp,
			),
		);

		foreach ($settings as $setting) {
			DB::table('settings')->insert($setting);
		}
	}

}