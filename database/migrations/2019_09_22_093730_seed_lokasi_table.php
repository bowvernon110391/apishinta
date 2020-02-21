<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SeedLokasiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('lokasi')->insert([
            ['nama'=>'T2F'],
            ['nama'=>'T3'],
            ['nama'=>'KANTOR'],
            // ['nama'=>'TPP'],
            // ['nama'=>'GUDANG']
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('lokasi')->truncate();
    }
}
