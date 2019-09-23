<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBc32DetailTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('bc32_detail', function(Blueprint $table)
		{
			$table->integer('id')->unsigned()->primary();
			$table->integer('bc32_header_id')->unsigned()->index('FK_bc32_detail_bc32_header');
			$table->string('jenis_valas', 8)->default('');
			$table->decimal('nilai_kurs', 18, 4)->default(0.0000);
			$table->decimal('denominasi', 18, 4)->default(0.0000);
			$table->decimal('jumlah', 18, 4)->default(0.0000);
			$table->decimal('total', 18, 4)->default(0.0000);
			$table->decimal('total_rupiah', 18, 4)->default(0.0000);
			$table->string('status', 50)->default('');
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
		Schema::drop('bc32_detail');
	}

}
