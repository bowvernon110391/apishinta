<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeyToCdHeaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cd_header', function (Blueprint $table) {
            // add foreign keys of pelabuhan kode
            $table->foreign('kd_pelabuhan_asal', 'kd_pelabuhan_fk1')->references('kode')->on('pelabuhan');
            $table->foreign('kd_pelabuhan_tujuan', 'kd_pelabuhan_fk2')->references('kode')->on('pelabuhan');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cd_header', function (Blueprint $table) {
            // drop foreigns
            $table->dropForeign('kd_pelabuhan_fk1');
            $table->dropForeign('kd_pelabuhan_fk2');
        });
    }
}
