<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFormsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('forms', function(Blueprint $table)
		{
			$table->increments('id');

			$table->string('slug', 120);
			$table->string('title');

			$table->text('fields');

			$table->boolean('save_records');

			$table->boolean('mail_records');
			$table->string('mail_records_address')->nullable();

			$table->boolean('mail_confirmation');

			$table->timestamps();
			$table->timestamp('activated_at')->nullable();
			$table->timestamp('deactivated_at')->nullable();
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
		Schema::drop('forms');
	}

}