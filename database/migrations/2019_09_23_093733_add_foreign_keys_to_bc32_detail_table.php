<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToBc32DetailTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('bc32_detail', function(Blueprint $table)
		{
			$table->foreign('bc32_header_id', 'FK_bc32_detail_bc32_header')->references('id')->on('bc32_header')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('bc32_detail', function(Blueprint $table)
		{
			$table->dropForeign('FK_bc32_detail_bc32_header');
		});
	}

}
