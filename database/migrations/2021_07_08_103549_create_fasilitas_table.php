<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFasilitasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fasilitas', function (Blueprint $table) {
            $table->bigIncrements('id');

            // detail barang yg mana?
            $table->unsignedBigInteger('detail_barang_id');

            // jenis fasilitas
            $table->enum('jenis', [
                'PEMBEBASAN',
                'TIDAK_DIPUNGUT',
                'KERINGANAN'
            ]);

            // utk pungutan yg mana
            $table->unsignedInteger('jenis_pungutan_id');

            // utk keringanan, mirip override tarif berarti
            $table->decimal('tarif_keringanan')->nullable();    // klo null brarti gk dpake

            // foreign keys
            $table->foreign('detail_barang_id')->references('id')->on('detail_barang');
            $table->foreign('jenis_pungutan_id')->references('id')->on('referensi_jenis_pungutan');

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
        Schema::dropIfExists('fasilitas');
    }
}
