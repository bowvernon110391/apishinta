<?php

use App\CD;
use App\PIBK;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SetDefaultPpnToOldPibkAndCdHeaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // set default ppn to old doc (pre 01/04/2022) to 10%
        CD::where('created_at', '<', '2022-04-01')->update([
            'ppn_tarif' => 10.0
        ]);

        PIBK::where('created_at', '<', '2022-04-01')->update([
            'ppn_tarif' => 10.0
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
