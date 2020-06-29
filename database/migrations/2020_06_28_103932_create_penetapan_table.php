<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePenetapanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('penetapan', function (Blueprint $table) {
            $table->bigIncrements('id');

            // what is being specified?
            $table->unsignedBigInteger('detail_barang_id')->nullable(); // refer to detail_barang (NULLABLE, in case of official assessment)
            $table->unsignedBigInteger('penetapan_id'); // refer to same table

            $table->unsignedInteger('pejabat_id')->nullable();  // refer to sso cache table

            $table->timestamps();

            // force index?
            $table->foreign('detail_barang_id', 'fk_pen_detail')->references('id')->on('detail_barang')->onDelete('cascade');
            $table->foreign('penetapan_id', 'fk_pen_penetapan')->references('id')->on('detail_barang')->onDelete('cascade');
            $table->foreign('pejabat_id', 'fk_pen_pejabat')->references('user_id')->on('sso_user_cache');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('penetapan');
    }
}
