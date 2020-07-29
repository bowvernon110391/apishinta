<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserToStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('status', function (Blueprint $table) {
            // add user id, nullable
            // $table->unsignedInteger('user_id')->nullable();

            // foreign key
            // ======================================================
            $table->foreign('user_id', 'status_user_id_fk_sso_user')->references('user_id')->on('sso_user_cache');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('status', function (Blueprint $table) {
            // revert it
            $table->dropForeign('status_user_id_fk_sso_user');
            // $table->dropColumn('user_id');
        });
    }
}
