<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDetailSekunderTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('detail_sekunder', function(Blueprint $table)
		{
			$table->increments('id');
			$table->enum('tipe', array('SERIAL_NUMBER','IMEI'));
			$table->integer('detail_id')->unsigned()->index('fk_detail_sekunder_detail_id_cd_detail_id');
			$table->text('data', 65535);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('detail_sekunder');
	}

}
