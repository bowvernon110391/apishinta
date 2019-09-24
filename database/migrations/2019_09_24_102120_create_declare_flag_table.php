<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeclareFlagTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('declare_flag', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nama')->index();
            $table->timestamps();
        });

        DB::table('declare_flag')->insert([
            ['nama'=> 'KARANTINA'],
            ['nama'=> 'NARKOTIKA'],
            ['nama'=> 'UANG'],
            ['nama'=> 'BKC'],
            ['nama'=> 'KOMERSIL'],
            ['nama'=> 'IMPOR_UNTUK_DIPAKAI']
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('declare_flag');
    }
}
