<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDetailBarangKategoriTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detail_barang_kategori', function (Blueprint $table) {
            $table->bigIncrements('id');

            // simply points to detail cd and kategori
            $table->unsignedBigInteger('detail_barang_id')->index();
            $table->unsignedInteger('kategori_id')->index();

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
        Schema::dropIfExists('detail_barang_kategori');
    }
}
