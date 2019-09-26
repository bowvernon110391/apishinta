<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSppHeaderTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('spp_header', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('cd_header_id')->unsigned()->index('fk_st_header_cd_id_cd_header_id');
			$table->integer('no_dok')->unsigned();
			$table->date('tgl_dok');
			$table->integer('lokasi_id')->unsigned()->index('fk_st_header_lokasi_id_lokasi_id');
			$table->decimal('total_fob', 18, 4)->nullable();
			$table->decimal('total_freight', 18, 4)->nullable();
			$table->decimal('total_insurance', 18, 4)->nullable();
			$table->decimal('total_cif', 18, 4)->nullable();
			$table->decimal('total_nilai_pabean', 18, 4)->nullable();
			$table->decimal('pembebasan', 18, 4)->nullable()->comment('dalam USD');
			$table->decimal('total_bm', 18, 4)->nullable();
			$table->decimal('total_ppn', 18, 4)->nullable();
			$table->decimal('total_ppnbm', 18, 4)->nullable();
			$table->decimal('total_pph', 18, 4)->nullable();
			$table->decimal('total_denda', 18, 4)->nullable();
			$table->text('keterangan', 65535)->nullable();
			$table->string('kode_valuta', 8)->nullable();
			$table->string('pemilik_barang')->nullable();
			$table->string('ground_handler')->nullable();
			$table->integer('pejabat_id')->unsigned();
			$table->decimal('nilai_valuta', 18, 4)->nullable();
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
		Schema::drop('spp_header');
	}

}
