<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMediaItemsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('media_items', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('file_type_id');
			$table->integer('media_type_id');
			$table->integer('user_id');

			$table->string('slug', 120);
			$table->string('title');
			$table->string('description_type', 8);
			$table->text('description');

			$table->boolean('hosted_externally');
			$table->string('hosted_content_type', 32)->nullable();
			$table->string('hosted_content_uri')->nullable();
			$table->string('hosted_content_thumbnail_url')->nullable();

			$table->string('filename')->nullable();
			$table->string('basename')->nullable();
			$table->string('extension', 12)->nullable();
			$table->string('path')->nullable();

			$table->integer('width')->nullable();
			$table->integer('height')->nullable();

			$table->boolean('thumbnail')->default(0);
			$table->string('thumbnail_extension', 12)->nullable();
			$table->integer('thumbnail_width')->nullable();
			$table->integer('thumbnail_height')->nullable();

			$table->boolean('comments_enabled');

			$table->date('date_created')->nullable();

			$table->timestamps();
			$table->timestamp('published_at')->nullable();
			$table->softDeletes();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('media_items');
	}

}