<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReferensiJenisPungutanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('referensi_jenis_pungutan', function (Blueprint $table) {
            $table->increments('id');

            // Pungutan punya empat data
            $table->string('kode', 8)->index(); // BM, PPN, PPh, etc
            $table->string('nama'); // deskripsi pungutan, eg: Bea Masuk
            $table->string('kode_akun')->unique();   // kode akun menurut DJPBN

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
        Schema::dropIfExists('referensi_jenis_pungutan');
    }
}
