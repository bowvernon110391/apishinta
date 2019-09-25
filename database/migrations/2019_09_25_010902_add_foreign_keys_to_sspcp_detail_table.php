<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToSspcpDetailTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('sspcp_detail', function(Blueprint $table)
		{
			$table->foreign('cd_detail_id', 'sspcp_detail_ibfk_1')->references('id')->on('cd_detail')->onUpdate('CASCADE')->onDelete('RESTRICT');
			$table->foreign('sspcp_header_id', 'sspcp_detail_ibfk_2')->references('id')->on('spp_header')->onUpdate('CASCADE')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('sspcp_detail', function(Blueprint $table)
		{
			$table->dropForeign('sspcp_detail_ibfk_1');
			$table->dropForeign('sspcp_detail_ibfk_2');
		});
	}

}
