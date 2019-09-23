<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLnfHeaderTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('lnf_header', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('pemilik_id')->unsigned()->nullable()->index('FK_lnf_header_penumpang');
			$table->integer('lokasi_id')->unsigned()->index('FK_lnf_header_lokasi');
			$table->string('no_flight', 50);
			$table->date('tgl_kedatangan');
			$table->dateTime('waktu_penemuan');
			$table->string('penemu');
			$table->text('catatan', 65535);
			$table->timestamps();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('lnf_header');
	}

}
