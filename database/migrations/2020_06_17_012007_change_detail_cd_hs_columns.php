<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeDetailCdHsColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cd_detail', function (Blueprint $table) {
            // first, we add the new column 'hs_id'
            $table->integer('hs_id')->unsigned()->nullable()->default(null)->after('hs_code');

            // make it refer to hs table?
            $table->foreign('hs_id', 'hs_id_hs_id')->references('id')->on('hs_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cd_detail', function (Blueprint $table) {
            // revert 
            $table->dropForeign('hs_id_hs_id');

            $table->dropColumn('hs_id');
        });
    }
}
