<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMediaTypesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('media_types', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('file_type_id')->nullable();
			$table->integer('parent_id')->nullable();

			$table->string('slug', 120);
			$table->string('name');
			$table->string('name_plural')->nullable();

			$table->text('extensions');
			$table->boolean('media_source_required');

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
		Schema::drop('media_types');
	}

}