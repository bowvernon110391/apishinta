<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddTimestampsToHsCode extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hs_code', function (Blueprint $table) {
            // add timestamps
            $table->timestamps();
            // soft delete to as effective date end
            $table->softDeletes();
        });

        // now we update the created at and the updated at?
        // use RAW SQL
        DB::update( DB::raw('UPDATE hs_code SET created_at = NOW(), updated_at = NOW()') );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hs_code', function (Blueprint $table) {
            // drop em?
            $table->dropTimestamps();
            $table->dropSoftDeletes();
        });
    }
}
