<?php

class PagesTableSeeder extends Seeder {

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
				'slug'       => 'home',
				'title'      => 'Home',
				'content'    => '<p>Home content coming soon.</p>',
				'active'     => true,
				'created_at' => $timestamp,
				'updated_at' => $timestamp,
			),
			array(
				'slug'       => 'about',
				'title'      => 'About Us',
				'content'    => '<p>About Us content coming soon.</p>',
				'active'     => true,
				'created_at' => $timestamp,
				'updated_at' => $timestamp,
			),
			array(
				'slug'       => 'contact',
				'title'      => 'Contact Us',
				'content'    => '<p>You may contact us at <strong>(403) 555-5555</strong> or by filling out the following form:</p><div>[INSERT VIEW "partials.forms.contact"]</div>',
				'active'     => true,
				'created_at' => $timestamp,
				'updated_at' => $timestamp,
			),
		);

		foreach ($pages as $page) {
			DB::table('content_pages')->insert($page);
		}
	}

}