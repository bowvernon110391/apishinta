<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeysToSppHeaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('spp_header', function (Blueprint $table) {
            // add some foreign
            $table->foreign('pejabat_id', 'spp_header_pejabat_id')->references('user_id')->on('sso_user_cache');
            $table->foreign('kd_negara_asal', 'spp_header_kd_negara_asal')->references('kode')->on('referensi_negara');
            $table->foreign('lokasi_id', 'spp_header_lokasi_id')->references('id')->on('lokasi');
            $table->foreign('cd_header_id', 'spp_header_cd_header_id')->references('id')->on('cd_header');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('spp_header', function (Blueprint $table) {
            // drop em all
            $table->dropForeign('spp_header_cd_header_id');
            $table->dropForeign('spp_header_lokasi_id');
            $table->dropForeign('spp_header_kd_negara_asal');
            $table->dropForeign('spp_header_pejabat_id');
        });
    }
}
