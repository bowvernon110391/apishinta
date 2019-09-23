<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCarnetDetailTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('carnet_detail', function(Blueprint $table)
		{
			$table->increments('id');
			$table->text('uraian_barang', 65535);
			$table->integer('jumlah_satuan')->unsigned();
			$table->string('jenis_satuan', 16);
			$table->decimal('brutto', 18, 4);
			$table->decimal('netto', 18, 4);
			$table->decimal('nilai_barang', 18, 4);
			$table->string('valuta', 8);
			$table->string('negara_asal');
			$table->string('hs_code', 16)->nullable();
			$table->string('catatan')->nullable();
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
		Schema::drop('carnet_detail');
	}

}
