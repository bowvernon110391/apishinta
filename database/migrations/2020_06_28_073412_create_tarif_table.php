<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTarifTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tarif', function (Blueprint $table) {
            $table->bigIncrements('id');

            // use polymorphic for tariffable
            $table->morphs('tariffable');
            // jenis pungutan (id)
            $table->unsignedInteger('jenis_pungutan_id');
            // jenis tarif (UNTUK BM ONLY) so nullable
            $table->enum('jenis', ['ADVALORUM', 'SPESIFIK'])->nullable()->default(null);
            // besar tarif (dalam 0-100% ADVALORUM, atau spesifik)
            $table->decimal('tarif', 18, 4);    // handle all case

            // besaran pembayaran (dibayar, dibebaskan, ditunda) totalnya harus 100%
            $table->decimal('bayar', 8, 4); // umumnya 100
            $table->decimal('bebas', 8, 4); // umumnya 0
            $table->decimal('tunda', 8, 4); // umumnya 0
            $table->decimal('tanggung_pemerintah', 8, 4); // umumnya 0

            $table->boolean('overridable')->default(true);

            $table->timestamps();

            // now index shits
            // $table->foreign('tariffable_id', 'fk1_penetapan')->references('id')->on('detail_barang')->onDelete('cascade');
            $table->foreign('jenis_pungutan_id', 'fk2_jenis_pungutan')->references('id')->on('referensi_jenis_pungutan');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tarif');
    }
}
