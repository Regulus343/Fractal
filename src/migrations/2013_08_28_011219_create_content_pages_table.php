<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContentPagesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('content_pages', function(Blueprint $table)
		{
			$table->increments('id');

			$table->string('slug', 120);
			$table->string('title');

			$table->integer('layout_template_id');
			$table->text('layout');

			$table->integer('user_id');
			$table->boolean('active');

			$table->timestamps();
			$table->timestamp('deleted_at');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('content_pages');
	}

}