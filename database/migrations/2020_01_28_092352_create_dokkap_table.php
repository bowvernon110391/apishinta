<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDokkapTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dokkap', function (Blueprint $table) {
            $table->bigIncrements('id');

            // data consists of no_dok + tgl_dok
            $table->string('no_dok')->index();
            $table->date('tgl_dok')->index();

            // jenis_dokkap_id references other table
            $table->integer('jenis_dokkap_id')->unsigned();
            $table->foreign('jenis_dokkap_id')->references('id')->on('referensi_jenis_dokkap');

            // it's polymorphic
            $table->morphs('master');

            // when was it created, etc
            $table->timestamps();
            // use soft deletion
            $table->softDeletes();
        });

        // set references?

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dokkap');
    }
}
