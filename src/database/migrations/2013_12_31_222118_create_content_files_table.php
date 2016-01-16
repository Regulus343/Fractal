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
			$table->integer('type_id')->nullable();
			$table->integer('image_template_id')->nullable();
			$table->string('name');

			$table->string('filename');
			$table->string('basename');
			$table->string('extension', 12);
			$table->string('path')->nullable();

			$table->integer('width')->nullable();
			$table->integer('height')->nullable();

			$table->boolean('thumbnail')->default(0);
			$table->integer('thumbnail_width')->nullable();
			$table->integer('thumbnail_height')->nullable();

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