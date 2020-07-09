<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeysToStHeaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('st_header', function (Blueprint $table) {
            // add some foreign
            $table->foreign('pejabat_id', 'st_header_pejabat_id')->references('user_id')->on('sso_user_cache');
            $table->foreign('kd_negara_asal', 'st_header_kd_negara_asal')->references('kode')->on('referensi_negara');
            $table->foreign('lokasi_id', 'st_header_lokasi_id')->references('id')->on('lokasi');
            $table->foreign('cd_header_id', 'st_header_cd_header_id')->references('id')->on('cd_header');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('st_header', function (Blueprint $table) {
            // drop em all
            $table->dropForeign('st_header_cd_header_id');
            $table->dropForeign('st_header_lokasi_id');
            $table->dropForeign('st_header_kd_negara_asal');
            $table->dropForeign('st_header_pejabat_id');
        });
    }
}
