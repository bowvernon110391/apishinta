<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToCdDetailTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('cd_detail', function(Blueprint $table)
		{
			$table->foreign('cd_header_id', 'fk_cd_detail_cd_id_cd_header_id')->references('id')->on('cd_header');//->onUpdate('CASCADE')->onDelete('RESTRICT');
			$table->foreign('kurs_id')->references('id')->on('kurs');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('cd_detail', function(Blueprint $table)
		{
			$table->dropForeign('fk_cd_detail_cd_id_cd_header_id');
		});
	}

}
