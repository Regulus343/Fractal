<?php

class FileMediaTypesTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		DB::table('file_types')->truncate();
		DB::table('media_types')->truncate();

		$timestamp = date('Y-m-d H:i:s');

		$fileTypes = [
			[
				'slug'        => 'images',
				'name'        => 'Image',
				'extensions'  => 'gif, jpg, png, svg',

				'media_types' => [
					[
						'slug' => 'photos',
						'name' => 'Photo',
					],
					[
						'slug' => 'artwork',
						'name' => 'Artwork',
					],
				],
			],
			[
				'slug'        => 'videos',
				'name'        => 'Video',
				'extensions'  => 'avi, flv, mkv, mp4, mpg',
			],
			[
				'slug'        => 'audio',
				'name'        => 'Audio',
				'extensions'  => 'ogg, mp3',

				'media_types' => [
					[
						'slug' => 'music',
						'name' => 'Music',
					],

					[
						'slug' => 'podcasts',
						'name' => 'Podcast',
					],
				],
			],
			[
				'slug'        => 'documents',
				'name'        => 'Document',
				'extensions'  => 'doc, docx, odt, pdf, txt',

				'media_types' => [
					[
						'slug' => 'book',
						'name' => 'Book',
					],
				],
			],
			[
				'slug'        => 'archives',
				'name'        => 'Archive',
				'extensions'  => 'rar, tar, tgz, zip',
			],
			[
				'slug'        => 'spreadsheets',
				'name'        => 'Spreadsheet',
				'extensions'  => 'csv, ods, xls, xlsx',
			],
			[
				'slug'        => 'code',
				'name'        => 'Code',
				'extensions'  => 'php, js, as, ts, py, c',
			],
		];

		foreach ($fileTypes as $fileType) {
			$mediaTypes = isset($fileType['media_types']) ? $fileType['media_types'] : [];

			if (isset($fileType['media_types']))
				unset($fileType['media_types']);

			$fileType['deletable']  = false;
			$fileType['created_at'] = $timestamp;
			$fileType['updated_at'] = $timestamp;

			$id = DB::table('file_types')->insertGetId($fileType);

			foreach ($mediaTypes as $mediaType) {
				$mediaType['file_type_id'] = $id;
				$mediaType['created_at']   = $timestamp;
				$mediaType['updated_at']   = $timestamp;

				DB::table('media_types')->insert($mediaType);
			}
		}
	}

}