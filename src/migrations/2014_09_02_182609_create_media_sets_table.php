<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMediaSetsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('media_sets', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('user_id');

			$table->string('slug', 120);
			$table->string('title');
			$table->string('description_type', 8);
			$table->text('description');

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
		Schema::drop('media_sets');
	}

}