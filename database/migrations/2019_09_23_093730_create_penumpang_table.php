<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePenumpangTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('penumpang', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('nama');
			$table->date('tgl_lahir');
			$table->string('pekerjaan');
			$table->string('no_paspor');
			$table->string('kebangsaan', 4)->index();
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
		Schema::drop('penumpang');
	}

}
