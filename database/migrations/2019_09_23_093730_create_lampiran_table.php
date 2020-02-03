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
			$table->string('resumable_upload_id');
			$table->enum('jenis',['GAMBAR','DOKUMEN','LAIN-LAIN']);
			$table->string('mime_type');
			$table->string('diskfilename')->unique('diskfilename');
			$table->string('filename');
			$table->morphs('attachable');

			// put a blob of base64 data because server is 
			// inode count limited
			$table->binary('blob')->nullable();

			// also store filesize?
			$table->integer('filesize')->unsigned()->default(0);

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
