<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBlogArticleContentAreas extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('blog_article_content_areas', function(Blueprint $table)
		{
			$table->increments('id');

			$table->integer('article_id');
			$table->integer('area_id');
			$table->string('layout_tag', 64);

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
		Schema::drop('blog_article_content_areas');
	}

}