<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AdjustLhpTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lhp', function (Blueprint $table) {
            // drop if exist
            if (Schema::hasColumn('lhp', 'nama_pejabat')) {
                $table->dropColumn('nama_pejabat');
                $table->dropColumn('nip_pejabat');

                $table->dropColumn('waktu_mulai_periksa');
                $table->dropColumn('waktu_selesai_periksa');
            }

            // add pemeriksa_id
            $table->integer('pemeriksa_id')->unsigned()->index()->after('lokasi_id');
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
            // do nothin
            $table->dropColumn('pemeriksa_id');
        });
    }
}
