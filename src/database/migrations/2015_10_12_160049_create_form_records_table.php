<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFormRecordsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('form_records', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('form_id');
			$table->integer('user_id')->nullable();

			$table->string('first_name', 120)->nullable();
			$table->string('last_name', 120)->nullable();
			$table->string('email')->nullable();

			$table->text('data');

			$table->timestamps();
			$table->timestamp('processed_at')->nullable();
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
		Schema::drop('form_records');
	}

}