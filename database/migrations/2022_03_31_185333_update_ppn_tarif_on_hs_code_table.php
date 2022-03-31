<?php

use App\HsCode;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatePpnTarifOnHsCodeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // update it
        HsCode::whereNotNull('ppn_tarif')->update([
            'ppn_tarif' => 11.0,
            'ppn_text' => '11'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
