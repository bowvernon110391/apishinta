<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToIsDetailTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('is_detail', function(Blueprint $table)
		{
			$table->foreign('cd_detail_id', 'FK_is_detail_cd_detail')->references('id')->on('cd_detail')->onUpdate('CASCADE')->onDelete('RESTRICT');
			$table->foreign('is_header_id', 'FK_is_detail_is_header')->references('id')->on('is_header')->onUpdate('CASCADE')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('is_detail', function(Blueprint $table)
		{
			$table->dropForeign('FK_is_detail_cd_detail');
			$table->dropForeign('FK_is_detail_is_header');
		});
	}

}
