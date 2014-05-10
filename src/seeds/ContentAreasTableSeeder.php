<?php

class ContentAreasTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		DB::table('content_areas')->truncate();
		DB::table('content_page_areas')->truncate();

		$timestamp = date('Y-m-d H:i:s');

		$areas = array(
			array(
				'title'        => 'Home',
				'content_type' => 'HTML',
				'content'      => '<p>Home content coming soon.</p>',
				'created_at'   => $timestamp,
				'updated_at'   => $timestamp,

				'page_areas'   => array(
					array(
						'page_id'    => 1,
						'area_id'    => 1,
						'layout_tag' => 'main',
						'created_at' => $timestamp,
						'updated_at' => $timestamp,
					),
				),
			),
			array(
				'title'        => 'About Us - Main',
				'content_type' => 'HTML',
				'content'      => '<p>About Us content coming soon.</p>',
				'created_at'   => $timestamp,
				'updated_at'   => $timestamp,

				'page_areas'   => array(
					array(
						'page_id'    => 2,
						'area_id'    => 2,
						'layout_tag' => 'main',
						'created_at' => $timestamp,
						'updated_at' => $timestamp,
					),
				),
			),
			array(
				'title'        => 'About Us - Side',
				'content_type' => 'HTML',
				'content'      => '<p>About Us side content coming soon.</p>',
				'created_at'   => $timestamp,
				'updated_at'   => $timestamp,

				'page_areas'   => array(
					array(
						'page_id'    => 2,
						'area_id'    => 3,
						'layout_tag' => 'side',
						'created_at' => $timestamp,
						'updated_at' => $timestamp,
					),
				),
			),
			array(
				'title'        => 'Contact Us',
				'content_type' => 'HTML',
				'content'      => '<p>You may contact us at <strong>(403) 555-5555</strong> or by filling out the following form:</p><div>[view:"fractal::pages.inserts.form_contact"]</div>',
				'created_at'   => $timestamp,
				'updated_at'   => $timestamp,

				'page_areas'   => array(
					array(
						'page_id'    => 3,
						'area_id'    => 4,
						'layout_tag' => 'main',
						'created_at' => $timestamp,
						'updated_at' => $timestamp,
					),
				),
			),
		);

		foreach ($areas as $area) {
			$pageAreas = $area['page_areas'];
			unset($area['page_areas']);

			DB::table('content_areas')->insert($area);

			foreach ($pageAreas as $pageArea) {
				DB::table('content_page_areas')->insert($pageArea);
			}
		}
	}

}