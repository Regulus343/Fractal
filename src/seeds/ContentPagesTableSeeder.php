<?php

class ContentPagesTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		DB::table('content_pages')->truncate();

		$timestamp = date('Y-m-d H:i:s');

		$pages = [
			[
				'slug'               => 'home',
				'title'              => 'Home',
				'layout_template_id' => 1,
				'layout'             => '',
			],
			[
				'slug'               => 'about',
				'title'              => 'About Us',
				'layout_template_id' => 2,
				'layout'             => '',
			],
			[
				'slug'               => 'contact',
				'title'              => 'Contact Us',
				'layout_template_id' => 1,
				'layout'             => '',
			],
		];

		foreach ($pages as $page) {
			$page['user_id']      = 1;
			$page['created_at']   = $timestamp;
			$page['updated_at']   = $timestamp;
			$page['published_at'] = $timestamp;

			DB::table('content_pages')->insert($page);
		}
	}

}