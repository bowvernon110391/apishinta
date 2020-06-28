<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePungutanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pungutan', function (Blueprint $table) {
            $table->bigIncrements('id');

            // first, a morph to designate which this belongs  to
            $table->morphs('dutiable');
            // next, what kind of levy is this?
            $table->unsignedInteger('jenis_pungutan_id');
            // payment detail
            $table->decimal('bayar', 18, 4);
            $table->decimal('bebas', 18, 4);
            $table->decimal('tunda', 18, 4);
            $table->decimal('tanggung_pemerintah', 18, 4);

            // pejabat yg menetapkan? null berarti bukan penetapan
            $table->unsignedInteger('pejabat_id');

            $table->timestamps();

            // =================FOREIGN KEYS==================================
            $table->foreign('jenis_pungutan_id', 'fk1_pung_jenis_pung')->references('id')->on('referensi_jenis_pungutan');
            $table->foreign('pejabat_id', 'fk2_pejabat_sso_user_cache')->references('user_id')->on('sso_user_cache');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pungutan');
    }
}
