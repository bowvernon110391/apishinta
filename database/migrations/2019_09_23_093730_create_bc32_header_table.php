<?php

use App\MigrationTraitDokumen;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBc32HeaderTable extends Migration {
	use MigrationTraitDokumen;

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('bc32_header', function(Blueprint $table)
		{
			$table->increments('id');

			$this->addDokumenColumns($table);

			$table->integer('cd_header_id')->unsigned();
			// $table->integer('no_dok')->unsigned();
			// $table->date('tgl_dok');
			$table->enum('fl_wakil', array('ORANG LAIN','PERUSAHAAN'))->nullable();
			$table->integer('pemilik_id')->unsigned()->nullable();
			$table->string('nama_perusahaan')->nullable();
			$table->string('alamat_perusahaan')->nullable();
			$table->enum('jenis_usaha', array('BANK','MONEY CHANGER','LAINNYA'))->nullable();
			$table->string('deskripsi_usaha')->nullable();
			$table->enum('fl_pembawaan', array('BERSAMA PENUMPANG','JASA PENGIRIMAN'))->nullable();
			$table->string('pelabuhan_asal')->nullable();
			$table->string('pelabuhan_tujuan')->nullable();
			$table->string('alamat_di_indonesia')->nullable();
			$table->enum('maksud_perjalanan', array('BISNIS','LIBURAN','BEKERJA','LAINNYA'))->nullable();
			$table->string('deskripsi_perjalanan')->nullable();
			$table->date('tgl_kirim')->nullable();
			$table->date('tgl_terima')->nullable();
			$table->string('cara_kirim')->nullable();
			$table->string('nama_jasa_pengiriman')->nullable();
			$table->text('pengirim', 65535)->nullable()->comment('nama dan alamat');
			$table->text('penerima', 65535)->nullable()->comment('nama dan alamat');
			$table->enum('fl_deklarasi', array('KEBERANGKATAN','KEDATANGAN'))->nullable();
			$table->enum('maksud_penggunaan', array('PRIBADI','BISNIS','PENDIDIKAN','LAINNYA'))->nullable();
			$table->string('deskripsi_penggunaan')->nullable();
			$table->enum('jumlah_sesuai', array('Y','N'))->nullable();
			$table->enum('hasil_pemeriksaan', array('Y','N'))->nullable();
			$table->enum('mencurigakan', array('Y','N'))->nullable();
			$table->integer('pemeriksa_id')->unsigned()->nullable();
			$table->string('no_ijin_bi')->nullable();
			$table->date('tgl_ijin_bi')->nullable();
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
		Schema::drop('bc32_header');
	}

}
