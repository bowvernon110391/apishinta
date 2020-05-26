<?php

use App\MigrationTraitDokumen;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePembatalansTable extends Migration
{
    use MigrationTraitDokumen;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pembatalans', function (Blueprint $table) {
            $table->bigIncrements('id');

            $this->addDokumenColumns($table);

            // tambah nip pejabat
            $table->string('nip_pejabat', 32);
            $table->string('nama_pejabat', 128);

            // keterangan
            $table->text('keterangan');

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
        Schema::dropIfExists('pembatalans');
    }
}
