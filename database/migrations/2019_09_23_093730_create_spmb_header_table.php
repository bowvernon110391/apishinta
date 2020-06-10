<?php

use App\MigrationTraitDokumen;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSpmbHeaderTable extends Migration {
	use MigrationTraitDokumen;

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('spmb_header', function(Blueprint $table)
		{
			$table->increments('id');

			$this->addDokumenColumns($table);

			$table->integer('cd_header_id')->unsigned()->index('fk_st_header_cd_id_cd_header_id');
			// $table->integer('no_dok')->unsigned();
			// $table->date('tgl_dok');
			$table->integer('lokasi_id')->unsigned()->index('fk_st_header_lokasi_id_lokasi_id');
			$table->decimal('total_fob', 18, 4)->nullable();
			$table->text('keterangan', 65535)->nullable();
			$table->string('kode_valuta', 8)->nullable();
			$table->string('pemilik_barang')->nullable();
			$table->string('no_tiket')->nullable();
			$table->string('maksud_pembawaan')->nullable();
			// $table->integer('pejabat_id')->unsigned();
			$table->decimal('nilai_valuta', 18, 4)->nullable();
			$table->decimal('total_brutto', 18, 4);
			$table->decimal('total_netto', 18, 4);

			$table->string('nama_pejabat');
			$table->string('nip_pejabat');

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
		Schema::drop('spmb_header');
	}

}
