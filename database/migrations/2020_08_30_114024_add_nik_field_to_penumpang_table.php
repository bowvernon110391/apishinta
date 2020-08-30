<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNikFieldToPenumpangTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('penumpang', function (Blueprint $table) {
            // add a string, nullable
            $table->string('nik')->nullable()->after('no_paspor');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('penumpang', function (Blueprint $table) {
            // just drop column
            $table->dropColumn('nik');
        });
    }
}
