<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToStHeaderTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('st_header', function(Blueprint $table)
		{
			$table->foreign('cd_header_id', 'fk_st_header_cd_id_cd_header_id')->references('id')->on('cd_header');//->onUpdate('CASCADE')->onDelete('RESTRICT');
			$table->foreign('lokasi_id', 'fk_st_header_lokasi_id_lokasi_id')->references('id')->on('lokasi');//->onUpdate('CASCADE')->onDelete('RESTRICT');
			// kode negara
			$table->foreign('kd_negara_asal', 'kd_ref_negara_st')->references('kode')->on('referensi_negara');
			// foreign index?
			$table->foreign('kurs_id', 'fk_st_header_kurs_id_kurs_id')->references('id')->on('kurs');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('st_header', function(Blueprint $table)
		{
			$table->dropForeign('fk_st_header_cd_id_cd_header_id');
			$table->dropForeign('fk_st_header_lokasi_id_lokasi_id');
			$table->dropForeign('fk_st_header_kurs_id_kurs_id');
			$table->dropForeign('kd_ref_negara_st');
		});
	}

}
