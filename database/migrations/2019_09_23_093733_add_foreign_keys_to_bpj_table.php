<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToBpjTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('bpj', function(Blueprint $table)
		{
			$table->foreign('lokasi_id', 'fk_bpj_lokasi_id_lokasi_id')->references('id')->on('lokasi');//->onUpdate('CASCADE')->onDelete('RESTRICT');
			$table->foreign('penumpang_id', 'fk_bpj_penumpang_id_penumpang_id')->references('id')->on('penumpang');//->onUpdate('CASCADE')->onDelete('RESTRICT');
			// $table->foreign('ndpbm_id')->references('id')->on('kurs');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('bpj', function(Blueprint $table)
		{
			$table->dropForeign('fk_bpj_lokasi_id_lokasi_id');
			$table->dropForeign('fk_bpj_penumpang_id_penumpang_id');
		});
	}

}
