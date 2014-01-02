<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFilesTable extends Migration {

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
			$table->integer('image_template_id');
			$table->string('name');
			$table->string('filename');
			$table->string('basename');
			$table->string('extension', 24);
			$table->string('path');
			$table->string('type', 64);
			$table->integer('width');
			$table->integer('height');
			$table->boolean('thumbnail');
			$table->integer('thumbnail_width');
			$table->integer('thumbnail_height');
			$table->integer('user_id');
			$table->timestamps();
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
