<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldPembebasanToDetailBarangTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('detail_barang', function (Blueprint $table) {
            // add pembebasan
            $table->decimal('pembebasan',18,4)->default(0)->after('kurs_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('detail_barang', function (Blueprint $table) {
            // remove it
            $table->dropColumn('pembebasan');
        });
    }
}
