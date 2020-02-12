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
            // add new column and subsequently index to it
            $table->string('kd_flight', 4);

            // add index
            $table->foreign('kd_flight')->references('kode')->on('airline');
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
            //
        });
    }
}
