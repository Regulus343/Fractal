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

		$areas = [
			[
				'title'        => 'Home',
				'content_type' => 'Markdown',
				'content'      => 'Welcome to the home page of a new website!',

				'page_areas'   => [
					[
						'page_id'    => 1,
						'area_id'    => 1,
						'layout_tag' => 'main',
					],
				],
			],
			[
				'title'        => 'About Us: Main',
				'content_type' => 'Markdown',
				'content'      => 'About Us content coming soon.',

				'page_areas'   => [
					[
						'page_id'    => 2,
						'area_id'    => 2,
						'layout_tag' => 'main',
					],
				],
			],
			[
				'title'        => 'About Us: Side',
				'content_type' => 'Markdown',
				'content'      => 'About Us side content coming soon.',

				'page_areas'   => [
					[
						'page_id'    => 2,
						'area_id'    => 3,
						'layout_tag' => 'side',
					],
				],
			],
			[
				'title'        => 'Contact Us',
				'content_type' => 'Markdown',
				'content'      => 'You may contact us at **(403) 555-5555** or by filling out the following form:'."\n\n".'[view:"fractal::content.pages.inserts.form_contact"]',

				'page_areas'   => [
					[
						'page_id'    => 3,
						'area_id'    => 4,
						'layout_tag' => 'main',
					],
				],
			],
		];

		foreach ($areas as $area) {
			$pageAreas = $area['page_areas'];
			unset($area['page_areas']);

			$area['user_id']    = 1;
			$area['created_at'] = $timestamp;
			$area['updated_at'] = $timestamp;

			DB::table('content_areas')->insert($area);

			foreach ($pageAreas as $pageArea) {
				$pageArea['created_at'] = $timestamp;
				$pageArea['updated_at'] = $timestamp;

				DB::table('content_page_areas')->insert($pageArea);
			}
		}
	}

}