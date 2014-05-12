<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContentFilesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('content_files', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('image_template_id')->default(0);
			$table->string('name');
			$table->string('filename');
			$table->string('basename');
			$table->string('extension', 24);
			$table->string('path')->nullable();
			$table->string('type', 64);

			$table->integer('width')->default(0);
			$table->integer('height')->default(0);

			$table->boolean('thumbnail')->default(0);
			$table->integer('thumbnail_width')->default(0);
			$table->integer('thumbnail_height')->default(0);

			$table->integer('user_id')->default(0);

			$table->timestamps();
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
		Schema::drop('content_files');
	}

}