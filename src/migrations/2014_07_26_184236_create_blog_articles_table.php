<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBlogArticlesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('blog_articles', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('blog_id');
			$table->integer('user_id');

			$table->string('slug', 120);
			$table->string('title');

			$table->integer('layout_template_id');
			$table->text('layout');

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
		Schema::drop('blog_articles');
	}

}