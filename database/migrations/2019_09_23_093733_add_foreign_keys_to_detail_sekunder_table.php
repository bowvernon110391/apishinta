<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToDetailSekunderTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('detail_sekunder', function(Blueprint $table)
		{
			$table->foreign('cd_detail_id', 'fk_detail_sekunder_detail_id_cd_detail_id')->references('id')->on('cd_detail')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('detail_sekunder', function(Blueprint $table)
		{
			$table->dropForeign('fk_detail_sekunder_detail_id_cd_detail_id');
		});
	}

}
