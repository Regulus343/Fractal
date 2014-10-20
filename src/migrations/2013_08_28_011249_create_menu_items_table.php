<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMenuItemsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('menu_items', function(Blueprint $table)
		{
			$table->increments('id');

			$table->integer('menu_id');
			$table->integer('parent_id')->nullable();
			$table->string('type', 32)->default('URI');
			$table->integer('page_id');
			$table->string('uri', 120);
			$table->string('subdomain', 64)->nullable();
			$table->string('label');
			$table->string('label_language_key')->nullable();
			$table->string('icon', 72);
			$table->string('class');
			$table->string('additional_info');
			$table->integer('display_order');
			$table->integer('auth_status');
			$table->string('auth_roles');
			$table->string('auth_permissions');
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