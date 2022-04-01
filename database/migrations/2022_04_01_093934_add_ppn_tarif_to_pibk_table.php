<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPpnTarifToPibkTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pibk', function (Blueprint $table) {
            // add ppn_tarif after ndpbm_id
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
        Schema::table('pibk', function (Blueprint $table) {
            // drop it
            $table->dropColumn('ppn_tarif');
        });
    }
}
