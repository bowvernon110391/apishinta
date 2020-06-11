<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPenumpangDataToSpmbHeaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('spmb_header', function (Blueprint $table) {
            // just add penumpang_id
            $table->integer('penumpang_id')->unsigned();
            $table->foreign('penumpang_id')->references('id')->on('penumpang');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('spmb_header', function (Blueprint $table) {
            Schema::disableForeignKeyConstraints();

            $table->dropColumn('penumpang_id');

            Schema::enableForeignKeyConstraints();
        });
    }
}
