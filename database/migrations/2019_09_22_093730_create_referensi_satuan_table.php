<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateReferensiSatuanTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('referensi_satuan', function(Blueprint $table)
		{
			$table->bigInteger('id')->primary();
			$table->string('kode', 4)->nullable()->index();
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
		Schema::drop('referensi_satuan');
	}

}
