<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserMutualBlockTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('userMutualBlock', function($table){
			$table->increments('id');
			$table->integer('index')->nullable();
			$table->string('uid', 16)->nullable();
			$table->string('sid', 24)->nullable();
			$table->integer('amount');
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
		Schema::drop('userMutualBlock');
	}

}
