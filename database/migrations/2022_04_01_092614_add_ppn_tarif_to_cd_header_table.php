<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPpnTarifToCdHeaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cd_header', function (Blueprint $table) {
            // add ppn_tarif maybe after ndpbm_id
            $table->decimal('ppn_tarif', 18, 4)->after('ndpbm_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cd_header', function (Blueprint $table) {
            // drop it
            $table->dropColumn('ppn_tarif');
        });
    }
}
