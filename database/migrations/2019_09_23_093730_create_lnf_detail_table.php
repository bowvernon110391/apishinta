<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLnfDetailTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('lnf_detail', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('lnf_header_id')->unsigned()->index('FK_lnf_detail_lnf_header');
			$table->text('uraian', 65535);
			$table->integer('jumlah_satuan')->unsigned();
			$table->integer('jumlah_kemasan')->unsigned();
			$table->string('jenis_satuan', 16);
			$table->string('jenis_kemasan', 16);
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
		Schema::drop('lnf_detail');
	}

}
