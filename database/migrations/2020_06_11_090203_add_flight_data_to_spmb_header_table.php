<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFlightDataToSpmbHeaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('spmb_header', function (Blueprint $table) {
            // add kolom no_flight + kd_airline
            if (!Schema::hasColumn('spmb_header', 'no_flight_berangkat')) $table->string('no_flight_berangkat', 64);
            if (!Schema::hasColumn('spmb_header', 'tgl_berangkat')) $table->date('tgl_berangkat');
            if (!Schema::hasColumn('spmb_header', 'kd_flight_berangkat')) $table->string('kd_flight_berangkat', 4);
            // add foreign key?
            if (Schema::hasColumn('spmb_header', 'kd_flight_berangkat')) $table->foreign('kd_flight_berangkat')->references('kode')->on('airline');
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
            // remove foreign?
            Schema::disableForeignKeyConstraints();

            // remove said columns?
            if (Schema::hasColumn('spmb_header', 'no_flight_berangkat')) $table->dropColumn('no_flight_berangkat');
            if (Schema::hasColumn('spmb_header', 'kd_flight_berangkat')) $table->dropColumn('kd_flight_berangkat');
            if (Schema::hasColumn('spmb_header', 'tgl_berangkat')) $table->dropColumn('tgl_berangkat');

            Schema::enableForeignKeyConstraints();
        });
    }
}
