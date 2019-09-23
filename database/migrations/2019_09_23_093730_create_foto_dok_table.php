<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFotoDokTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('foto_dok', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('judul_foto');
			$table->string('filename')->unique('filename');
			$table->integer('header_id')->unsigned()->index('FK_foto_spmb_spmb_header');
			$table->enum('jenis_dok', array('CD','ST','SPP','SPMB','IS','BC32'))->default('IS')->index('jenis_dok');
			$table->dateTime('created_at')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('foto_dok');
	}

}
