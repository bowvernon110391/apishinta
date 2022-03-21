<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateSourceInSppHeaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // run sql update statement?
        DB::table('spp_header as s')
            ->whereNull('s.source_type')
            ->update([
                's.source_type' => 'App\\CD',
                's.source_id' => DB::raw('s.cd_header_id')
            ]);

        /* Schema::table('spp_header', function (Blueprint $table) {
        }); */
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        /* Schema::table('spp_header', function (Blueprint $table) {
            //
        }); */
    }
}
