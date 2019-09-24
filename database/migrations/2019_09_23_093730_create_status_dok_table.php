<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateStatusDokTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('status_dok', function(Blueprint $table)
		{
			$table->bigIncrements('id');
			$table->string('status');
			$table->integer('statusable_id')->unsigned()->index();
			$table->string('statusable_type')->index();
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
		Schema::drop('status_dok');
	}

}
