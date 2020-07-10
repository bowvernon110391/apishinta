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
            ['kode'=>'T2F', 'nama'=>"Terminal Kedatangan Internasional 2F"],
            ['kode'=>'T3', 'nama'=>"Terminal Kedatangan Internasional 3"],
            ['kode'=>'BCSH', 'nama'=>"KPU BC Soekarno Hatta"],
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
