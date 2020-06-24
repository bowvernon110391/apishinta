<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveUnnecessaryColumnFromIp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('instruksi_pemeriksaans', function (Blueprint $table) {
            // remove only if exists
            if(Schema::hasColumn('instruksi_pemeriksaans', 'nama_importir')) {
                $table->dropColumn('nama_importir');
                $table->dropColumn('npwp_importir');
                $table->dropColumn('nama_kuasa');
                $table->dropColumn('identitas_kuasa');
            }
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
            //
        });
    }
}
