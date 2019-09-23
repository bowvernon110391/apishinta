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
			$table->integer('id')->unsigned()->primary();
			$table->integer('spmb_header_id')->unsigned()->index('fk_st_detail_st_header_id_st_header_id');
			$table->integer('cd_detail_id')->unsigned()->index('FK_spmb_detail_cd_detail');
			$table->decimal('fob', 18, 4)->nullable();
			$table->text('keterangan', 65535)->nullable();
			$table->string('kode_valuta', 8)->nullable();
			$table->string('hs_code', 16)->nullable();
			$table->decimal('nilai_valuta', 18, 4)->nullable();
			$table->decimal('brutto', 18, 4);
			$table->decimal('netto', 18, 4);
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
		Schema::drop('spmb_detail');
	}

}
