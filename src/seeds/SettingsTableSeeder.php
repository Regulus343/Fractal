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

		$settings = [

			/* General */

			[
				'name'          => 'Website Name',
				'value'         => 'Fractal',
				'type'          => 'Text',
				'rules'         => 'required',
				'display_order' => 1,
			],
			[
				'name'          => 'Webmaster Email',
				'value'         => 'admin@website.com',
				'type'          => 'Text',
				'rules'         => 'required, email',
				'display_order' => 2,
			],
			[
				'name'          => 'CMS Roles',
				'value'         => 'admin, mod',
				'type'          => 'List',
				'options'       => '\Regulus\Identify\Role::getSelectable()',
				'developer'     => true,
			],
			[
				'name'          => 'Auto Format Titles',
				'value'         => true,
				'type'          => 'Boolean',
				'rules'         => '',
				'developer'     => true,
			],
			[
				'name'          => 'Items Listed Per Page',
				'value'         => 20,
				'type'          => 'Integer',
				'options'       => '5:300; +5',
			],
			[
				'name'          => 'Content View Logging Type',
				'value'         => 'Unique',
				'type'          => 'Text',
				'options'       => 'None, Unique, All',
				'developer'     => true,
			],
			[
				'name'          => 'Content Download Logging Type',
				'value'         => 'Unique',
				'type'          => 'Text',
				'options'       => 'None, Unique, All',
				'developer'     => true,
			],
			[
				'name'          => 'Display Unique Content Views',
				'value'         => true,
				'type'          => 'Boolean',
				'developer'     => true,
			],
			[
				'name'          => 'Display Unique Content Downloads',
				'value'         => true,
				'type'          => 'Boolean',
				'developer'     => true,
			],

			/* Pages */

			[
				'name'          => 'Default Content Area Type',
				'value'         => 'Markdown',
				'type'          => 'Text',
				'category'      => 'Pages',
				'options'       => 'HTML, Markdown',
			],

			/* Files */

			[
				'name'          => 'Default Image Thumbnail Size',
				'value'         => 200,
				'type'          => 'Integer',
				'category'      => 'Files',
				'rules'         => '',
				'developer'     => true,
			],
			[
				'name'          => 'Default Media Image Width',
				'value'         => 1024,
				'type'          => 'Integer',
				'category'      => 'Files',
				'rules'         => '',
				'developer'     => true,
			],
			[
				'name'          => 'Default Media Image Height',
				'value'         => 768,
				'type'          => 'Integer',
				'category'      => 'Files',
				'rules'         => '',
				'developer'     => true,
			],
			[
				'name'          => 'Image Resize Quality',
				'value'         => 65,
				'type'          => 'Integer',
				'category'      => 'Files',
				'options'       => '1:100',
				'developer'     => true,
			],

			/* Users */

			[
				'name'          => 'Minimum Password Length',
				'value'         => 8,
				'type'          => 'Integer',
				'category'      => 'Users',
				'options'       => '6:24; +2',
				'rules'         => 'required, numeric',
				'developer'     => true,
			],
			[
				'name'          => 'Require Unique Email Addresses',
				'value'         => true,
				'type'          => 'Boolean',
				'category'      => 'Users',
				'developer'     => true,
			],
			[
				'name'          => 'Image Resize Quality',
				'value'         => 65,
				'type'          => 'Integer',
				'category'      => 'Files',
				'options'       => '1:100',
				'developer'     => true,
			],

			/* Blogs */

			[
				'name'          => 'Show Thumbnail Images on Article List',
				'value'         => true,
				'type'          => 'Boolean',
				'category'      => 'Blogs',
				'developer'     => true,
			],
			[
				'name'          => 'Show Placeholder Thumbnail Images on Article List',
				'value'         => true,
				'type'          => 'Boolean',
				'category'      => 'Blogs',
				'developer'     => true,
			],
		];

		foreach ($settings as $setting) {
			if (!isset($setting['display_order']))
				$setting['display_order'] = $defaultDisplayOrder;

			$setting['created_at'] = $timestamp;
			$setting['updated_at'] = $timestamp;

			DB::table('settings')->insert($setting);
		}
	}

}