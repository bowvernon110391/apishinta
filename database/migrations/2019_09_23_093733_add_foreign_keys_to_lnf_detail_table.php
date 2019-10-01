<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToLnfDetailTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('lnf_detail', function(Blueprint $table)
		{
			$table->foreign('lnf_header_id', 'FK_lnf_detail_lnf_header')->references('id')->on('lnf_header');//->onUpdate('CASCADE')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('lnf_detail', function(Blueprint $table)
		{
			$table->dropForeign('FK_lnf_detail_lnf_header');
		});
	}

}
