<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('settings', function(Blueprint $table)
		{
			$table->increments('id');

			$table->string('name', 120);
			$table->string('label', 120);
			$table->string('value');
			$table->string('type', 24);
			$table->string('category', 120)->nullable();
			$table->text('options')->nullable();
			$table->text('rules')->nullable();
			$table->boolean('developer')->default(0);
			$table->integer('display_order')->default(50);

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
		Schema::drop('settings');
	}

}