<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContentLayoutTemplatesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('content_layout_templates', function(Blueprint $table)
		{
			$table->increments('id');

			$table->string('name', 120);
			$table->text('layout');
			$table->boolean('static');

			$table->integer('user_id');

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
		Schema::drop('content_layout_templates');
	}

}