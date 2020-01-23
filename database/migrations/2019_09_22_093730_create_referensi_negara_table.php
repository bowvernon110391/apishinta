<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
			$table->integer('id')->primary();
			$table->string('kode', 4)->unique();
			$table->string('kode_alpha3', 4)->unique();
			$table->string('uraian')->nullable();
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
