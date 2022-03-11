<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveCdHeaderIdColumnFromSppHeaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('spp_header', function (Blueprint $table) {
            $table->dropForeign('spp_header_cd_header_id');
            $table->dropIndex('fk_st_header_cd_id_cd_header_id');
            // drop column
            $table->dropColumn('cd_header_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('spp_header', function (Blueprint $table) {
            // no backsies!
        });
    }
}
