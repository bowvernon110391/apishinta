<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeNomorBillingUnique extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('billing', function (Blueprint $table) {
            // simply add unique to billing table
            $table->unique('nomor','unique_nomor_billing');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('billing', function (Blueprint $table) {
            // remove unique
            $table->dropUnique('unique_nomor_billing');
        });
    }
}
