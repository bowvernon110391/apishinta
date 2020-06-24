<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddKolomFotoToIp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('instruksi_pemeriksaans', function (Blueprint $table) {
            // add kolom foto after ajukan contoh
            $table->enum('ajukan_foto', ['Y', 'T'])->default('T')->after('ajukan_contoh');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('instruksi_pemeriksaans', function (Blueprint $table) {
            // drop it
            $table->dropColumn('ajukan_foto');
        });
    }
}
