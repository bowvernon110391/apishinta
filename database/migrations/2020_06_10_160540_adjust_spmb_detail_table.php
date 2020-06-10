<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AdjustSpmbDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('spmb_detail', function (Blueprint $table) {
            // modify some columns to default to null
            $table->integer('cd_detail_id')->unsigned()->nullable()->default(null)->change();
            $table->integer('kurs_id')->unsigned()->nullable()->default(null)->change();
            $table->decimal('brutto', 18, 4)->nullable()->default(null)->change();
            $table->decimal('harga_satuan', 18, 4)->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('spmb_detail', function (Blueprint $table) {
            //
        });
    }
}
