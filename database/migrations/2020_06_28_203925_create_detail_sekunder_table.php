<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDetailSekunderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detail_sekunder', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedInteger('jenis_detail_sekunder_id');
            $table->unsignedBigInteger('detail_barang_id');
            $table->text('data');

            $table->timestamps();

            // ==========FOREIGN KEYS================================
            $table->foreign('jenis_detail_sekunder_id', 'fk1_jenis_det_sek')->references('id')->on('referensi_jenis_detail_sekunder');
            $table->foreign('detail_barang_id', 'fk2_detail_brg_det_sek')->references('id')->on('detail_barang')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('detail_sekunder');
    }
}
