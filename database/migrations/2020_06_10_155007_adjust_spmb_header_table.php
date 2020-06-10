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
            $table->dropColumn('total_brutto');
            $table->dropColumn('total_fob');
            $table->dropColumn('total_netto');
            $table->dropColumn('nilai_valuta');
            $table->dropColumn('no_tiket');
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
