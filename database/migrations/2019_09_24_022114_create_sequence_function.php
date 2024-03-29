<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateSequenceFunction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // DB::unprepared('SET GLOBAL log_bin_trust_function_creators =1');
        DB::unprepared('DROP FUNCTION IF EXISTS getSequence;');
        $queryString = file_get_contents(dirname(__FILE__).'/fn_getSequence.sql');
        DB::unprepared($queryString);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP FUNCTION getSequence;');
    }
}
