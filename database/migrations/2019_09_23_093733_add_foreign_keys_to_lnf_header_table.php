<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToLnfHeaderTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('lnf_header', function(Blueprint $table)
		{
			$table->foreign('lokasi_id', 'FK_lnf_header_lokasi')->references('id')->on('lokasi')->onUpdate('RESTRICT')->onDelete('RESTRICT');
			$table->foreign('pemilik_id', 'FK_lnf_header_penumpang')->references('id')->on('penumpang')->onUpdate('CASCADE')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('lnf_header', function(Blueprint $table)
		{
			$table->dropForeign('FK_lnf_header_lokasi');
			$table->dropForeign('FK_lnf_header_penumpang');
		});
	}

}
