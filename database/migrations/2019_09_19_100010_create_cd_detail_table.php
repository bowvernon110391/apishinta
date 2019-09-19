<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCdDetailTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cd_detail', function(Blueprint $table)
		{
			$table->increments('id');
			$table->set('kategori', ['HVG','Alat Telekomunikasi','Media Informasi']);
			$table->integer('cd_id')->unsigned();
			$table->text('uraian', 65535);
			$table->integer('jumlah_satuan')->unsigned();
			$table->integer('jumlah_kemasan')->unsigned();
			$table->string('jenis_kemasan', 16);
			$table->string('jenis_satuan', 16);
			$table->string('hs_code', 16);
			$table->decimal('fob', 18, 4);
			$table->decimal('freight', 18, 4);
			$table->decimal('insurance', 18, 4);
			$table->string('kode_valuta', 8);
			$table->decimal('nilai_valuta', 18, 4);
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
		Schema::drop('cd_detail');
	}

}
