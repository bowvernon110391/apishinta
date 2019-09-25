<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SeedKategoriTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('kategori')->insert([
            ['nama'=>'High Value Goods'],
            ['nama'=>'Alat Telekomunikasi'],
            ['nama'=>'Media Informasi']
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        DB::table('kategori')->truncate();
    }
}
