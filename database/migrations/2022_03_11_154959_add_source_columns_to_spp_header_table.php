<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSourceColumnsToSppHeaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('spp_header', function (Blueprint $table) {
            // add source after cd_header_id
            $name = 'source';
            $table->string("{$name}_type")->nullable()->after('cd_header_id');
            $table->unsignedBigInteger("{$name}_id")->nullable()->after('cd_header_id');

            $table->index(["{$name}_type", "{$name}_id"]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('spp_header', function (Blueprint $table) {
            // drop source
            $table->dropMorphs('source');
        });
    }
}
