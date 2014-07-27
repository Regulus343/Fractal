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

		$pages = array(
			array(
				'slug'               => 'home',
				'title'              => 'Home',
				'layout_template_id' => 1,
				'layout'             => '',
				'created_at'         => $timestamp,
				'updated_at'         => $timestamp,
				'published_at'       => $timestamp,
			),
			array(
				'slug'               => 'about',
				'title'              => 'About Us',
				'layout_template_id' => 2,
				'layout'             => '',
				'created_at'         => $timestamp,
				'updated_at'         => $timestamp,
				'published_at'       => $timestamp,
			),
			array(
				'slug'               => 'contact',
				'title'              => 'Contact Us',
				'layout_template_id' => 1,
				'layout'             => '',
				'created_at'         => $timestamp,
				'updated_at'         => $timestamp,
				'published_at'       => $timestamp,
			),
		);

		foreach ($pages as $page) {
			DB::table('content_pages')->insert($page);
		}
	}

}