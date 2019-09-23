<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToSppDetailTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('spp_detail', function(Blueprint $table)
		{
			$table->foreign('cd_detail_id', 'FK_spp_detail_cd_detail')->references('id')->on('cd_detail')->onUpdate('CASCADE')->onDelete('RESTRICT');
			$table->foreign('spp_header_id', 'spp_detail_ibfk_1')->references('id')->on('spp_header')->onUpdate('CASCADE')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('spp_detail', function(Blueprint $table)
		{
			$table->dropForeign('FK_spp_detail_cd_detail');
			$table->dropForeign('spp_detail_ibfk_1');
		});
	}

}
