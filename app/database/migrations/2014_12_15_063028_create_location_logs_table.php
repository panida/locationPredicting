<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLocationLogsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('locationLogs', function($table)
		{
			$table->increments('id');
			$table->double('latitude');
			$table->double('longitude');
			$table->dateTime('dateTime');
			$table->integer('personId');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('locationLogs');
	}

}
