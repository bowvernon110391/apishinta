<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexToCancellableTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cancellable', function (Blueprint $table) {
            //
            $table->foreign('pembatalan_id', 'fk_pembatalan_id')->references('id')->on('pembatalans');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cancellable', function (Blueprint $table) {
            //
            $table->dropForeign('fk_pembatalan_id');
        });
    }
}
