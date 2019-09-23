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
			$table->foreign('cd_id', 'fk_st_header_cd_id_cd_header_id')->references('id')->on('cd_header')->onUpdate('CASCADE')->onDelete('RESTRICT');
			$table->foreign('lokasi_id', 'fk_st_header_lokasi_id_lokasi_id')->references('id')->on('lokasi')->onUpdate('CASCADE')->onDelete('RESTRICT');
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
		});
	}

}
