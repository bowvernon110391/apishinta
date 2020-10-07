<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePrmDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prm_data', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->json('raw');    // the raw data
            $table->unsignedInteger('petugas_id');  // who did it
            $table->unsignedInteger('cd_header_id')->nullable(); // the cd_header_id, it will be non zero when fully transformed
            $table->time('transformed')->nullable();    // when did the transformation happen

            $table->timestamps();

            // gib foreign constraint and shiet
            $table->foreign('petugas_id')->references('user_id')->on('sso_user_cache');
            $table->foreign('cd_header_id')->references('id')->on('cd_header');
            $table->index('transformed');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prm_data');
    }
}
