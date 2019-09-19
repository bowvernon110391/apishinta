<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToStDetailTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('st_detail', function(Blueprint $table)
		{
			$table->foreign('st_header_id', 'fk_st_detail_st_header_id_st_header_id')->references('id')->on('st_header')->onUpdate('CASCADE')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('st_detail', function(Blueprint $table)
		{
			$table->dropForeign('fk_st_detail_st_header_id_st_header_id');
		});
	}

}
