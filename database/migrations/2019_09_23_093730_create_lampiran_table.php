<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLampiranTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('lampiran', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('judul');
			$table->enum('jenis', ['GAMBAR', 'DOKUMEN', 'LAINNYA']);
			$table->string('mime_type');
			$table->string('filename')->unique('filename');
			$table->morphs('attachable');
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
		Schema::drop('lampiran');
	}

}
