<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEmailHpToPenumpangTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('penumpang', function (Blueprint $table) {
            // add email and phone
            $table->string('email')->after('kebangsaan')->index();
            $table->string('phone')->after('kebangsaan')->index();

            // nulling the pekerjaan
            $table->string('pekerjaan')->nullable()->change();
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
            // delete column
            $table->dropColumn('email');
            $table->dropColumn('phone');
        });
    }
}
