<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCarnetHeaderTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('carnet_header', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('no_flight', 50);
			$table->date('tgl_kedatangan');
			$table->integer('petugas_kedatangan_id')->unsigned();
			$table->integer('lokasi_kedatangan_id')->unsigned();
			$table->integer('penumpang_id')->unsigned();
			$table->string('no_carnet');
			$table->date('tgl_carnet')->nullable();
			$table->string('holder');
			$table->text('alamat_holder', 65535)->nullable();
			$table->string('representatif')->nullable();
			$table->string('maksud_penggunaan')->nullable();
			$table->string('penerbit');
			$table->date('tgl_kadaluarsa');
			$table->text('catatan_kedatangan', 65535)->nullable();
			$table->integer('petugas_keberangkatan_id')->unsigned()->nullable();
			$table->date('tgl_keberangkatan')->nullable();
			$table->date('tgl_perkiraan_keberangkatan')->nullable();
			$table->integer('lokasi_keberangkatan_id')->nullable();
			$table->enum('fl_pemeriksaan', array('Y','N'))->nullable();
			$table->enum('fl_sesuai', array('Y','N'))->nullable();
			$table->text('catatan_keberangkatan', 65535)->nullable();
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
		Schema::drop('carnet_header');
	}

}
