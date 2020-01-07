<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBpjTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('bpj', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('nomor')->nullable();
			$table->date('tanggal')->nullable();
			$table->string('penjamin')->nullable();
			$table->string('npwp')->nullable();
			$table->string('alamat')->nullable();
			$table->string('nomor_jaminan')->nullable();
			$table->date('tanggal_jaminan')->nullable();
			$table->string('bentuk_jaminan')->nullable();
			$table->decimal('jumlah', 18, 4)->nullable();
			$table->enum('jenis', array('TUNAI','TERTULIS'))->nullable();
			$table->date('tanggal_jatuh_tempo')->nullable();
			$table->integer('pembuat_id')->nullable();
			$table->boolean('active')->nullable();
			$table->enum('status', array('AKTIF','DICAIRKAN','BATAL'))->nullable()->default('AKTIF');
			
			$table->string('no_bukti_pengembalian')->nullable();
			$table->date('tgl_bukti_pengembalian')->nullable();
			$table->string('kode_agenda')->nullable();

			$table->string('catatan')->nullable();

			$table->integer('lokasi_id')->unsigned();
			$table->integer('penumpang_id')->unsigned();

			// polymorphs
			// $table->string('guaranteeable_type')->nullable();
			// $table->integer('guaranteeable_id')->unsigned()->nullable();
			$table->morphs('guaranteeable');

			$table->timestamps();
			$table->softDeletes();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('bpj');
	}

}
