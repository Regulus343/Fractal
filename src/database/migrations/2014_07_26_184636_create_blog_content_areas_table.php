<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBlogContentAreasTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('blog_content_areas', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('blog_id');
			$table->integer('user_id');

			$table->string('title');
			$table->string('content_type', 8);
			$table->text('content');

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
		Schema::drop('blog_content_areas');
	}

}