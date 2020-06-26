<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWaktuPemeriksaanLhp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lhp', function (Blueprint $table) {
            // add two datetime column
            $table->dateTime('waktu_mulai_periksa')->index();
            $table->dateTime('waktu_selesai_periksa')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lhp', function (Blueprint $table) {
            // remove said columns
            if (Schema::hasColumns('lhp', [
                'waktu_selesai_periksa',
                'waktu_mulai_periksa'
            ])) {
                $table->dropColumn('waktu_selesai_periksa');
                $table->dropColumn('waktu_mulai_periksa');
            }
        });
    }
}
