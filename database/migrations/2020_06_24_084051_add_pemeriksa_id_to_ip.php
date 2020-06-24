<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPemeriksaIdToIp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('instruksi_pemeriksaans', function (Blueprint $table) {
            // add after ajukan_foto
            $table->integer('pemeriksa_id')->unsigned()->after('ajukan_foto')->index();
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
            // drop
            $table->dropColumn('pemeriksa_id');
        });
    }
}
