<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToSpmbHeaderTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('spmb_header', function(Blueprint $table)
		{
			$table->foreign('cd_header_id', 'spmb_header_ibfk_1')->references('id')->on('cd_header')->onUpdate('CASCADE')->onDelete('RESTRICT');
			$table->foreign('lokasi_id', 'spmb_header_ibfk_2')->references('id')->on('lokasi')->onUpdate('CASCADE')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('spmb_header', function(Blueprint $table)
		{
			$table->dropForeign('spmb_header_ibfk_1');
			$table->dropForeign('spmb_header_ibfk_2');
		});
	}

}
