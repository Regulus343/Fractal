<?php

use Illuminate\Database\Migrations\Migration;

class CreateMenuItemsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('menu_items', function($table)
		{
			$table->increments('id');
			$table->integer('menu_id');
			$table->integer('parent_id');
			$table->integer('page_id');
			$table->string('uri', 120);
			$table->string('label');
			$table->string('icon', 72);
			$table->string('class');
			$table->string('additional_info');
			$table->integer('display_order');
			$table->integer('auth_status');
			$table->string('auth_roles');
			$table->boolean('active');
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
		Schema::drop('menu_items');
	}

}