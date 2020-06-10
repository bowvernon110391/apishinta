<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSpmbDetailTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('spmb_detail', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('cd_detail_id')->unsigned();
			$table->integer('spmb_header_id')->unsigned()->index('fk_st_detail_st_header_id_st_header_id');
			$table->text('uraian');
			$table->integer('jumlah_satuan')->unsigned();
			$table->integer('jumlah_kemasan')->unsigned();
			$table->string('jenis_kemasan', 16);
			$table->string('jenis_satuan', 16);
			// $table->integer('cd_detail_id')->unsigned()->index('FK_spmb_detail_cd_detail');
			$table->decimal('fob', 18, 4)->nullable();
			// $table->string('kode_valuta', 8)->nullable();
			// $table->string('hs_code', 16)->nullable();
			$table->integer('kurs_id')->unsigned();
			// $table->decimal('nilai_valuta', 18, 4)->nullable();
			$table->decimal('brutto', 18, 4);

			$table->decimal('harga_satuan', 18, 4);
			$table->text('keterangan');
			// $table->decimal('netto', 18, 4);
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
		Schema::drop('spmb_detail');
	}

}
