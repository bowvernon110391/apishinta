<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToSpmbDetailTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('spmb_detail', function(Blueprint $table)
		{
			$table->foreign('cd_detail_id', 'FK_spmb_detail_cd_detail')->references('id')->on('cd_detail');//->onUpdate('CASCADE')->onDelete('RESTRICT');
			$table->foreign('spmb_header_id', 'spmb_detail_ibfk_1')->references('id')->on('spmb_header');//->onUpdate('CASCADE')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('spmb_detail', function(Blueprint $table)
		{
			$table->dropForeign('FK_spmb_detail_cd_detail');
			$table->dropForeign('spmb_detail_ibfk_1');
		});
	}

}
