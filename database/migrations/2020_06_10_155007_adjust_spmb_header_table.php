<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AdjustSpmbHeaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('spmb_header', function (Blueprint $table) {
            // change some
            $table->integer('cd_header_id')->unsigned()->nullable()->default(null)->change();

            // remove columns
            if (Schema::hasColumn('spmb_header', 'total_brutto')) $table->dropColumn('total_brutto');
            if (Schema::hasColumn('spmb_header', 'total_fob')) $table->dropColumn('total_fob');
            if (Schema::hasColumn('spmb_header', 'total_netto')) $table->dropColumn('total_netto');
            if (Schema::hasColumn('spmb_header', 'nilai_valuta')) $table->dropColumn('nilai_valuta');
            if (Schema::hasColumn('spmb_header', 'kode_valuta')) $table->dropColumn('kode_valuta');
            if (Schema::hasColumn('spmb_header', 'no_tiket')) $table->dropColumn('no_tiket');
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
            //
        });
    }
}
