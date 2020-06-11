<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMissingColumnsToSpmbHeaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('spmb_header', function (Blueprint $table) {
            // kd_negara tujuan
            $table->string('kd_negara_tujuan', 4);
            $table->foreign('kd_negara_tujuan')->references('kode')->on('referensi_negara');

            // change maksud pembawaan to text columns
            $table->text('maksud_pembawaan')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('spmb_header', function (Blueprint $table) {
            // drop kode negara
            Schema::disableForeignKeyConstraints();

            $table->dropForeign('spmb_header_kd_negara_tujuan_foreign');
            $table->dropColumn('kd_negara_tujuan');

            Schema::enableForeignKeyConstraints();
        });
    }
}
