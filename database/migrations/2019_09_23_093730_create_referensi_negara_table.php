<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateReferensiNegaraTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('referensi_negara', function(Blueprint $table)
		{
			$table->bigInteger('ID')->primary();
			$table->string('KODE_NEGARA')->nullable();
			$table->string('URAIAN_NEGARA')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('referensi_negara');
	}

}
