<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContentPageAreasTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('content_page_areas', function(Blueprint $table)
		{
			$table->increments('id');

			$table->integer('page_id');
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
		Schema::drop('content_page_areas');
	}

}
