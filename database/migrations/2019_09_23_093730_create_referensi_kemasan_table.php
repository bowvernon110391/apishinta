<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateReferensiKemasanTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('referensi_kemasan', function(Blueprint $table)
		{
			$table->bigInteger('ID')->primary();
			$table->string('KODE_KEMASAN')->nullable();
			$table->string('URAIAN_KEMASAN')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('referensi_kemasan');
	}

}
