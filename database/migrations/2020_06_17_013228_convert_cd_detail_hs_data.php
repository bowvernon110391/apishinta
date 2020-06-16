<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ConvertCdDetailHsData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // do a destructive change!!
        $details = App\DetailCD::get();

        foreach ($details as $d) {
            $hs = App\HsCode::byExactHS($d->hs_code)->first();


            if ($hs) {
                echo "Change {$d->id} to use hs #{$hs->id} now\n";
                $d->hs()->associate(App\HsCode::byExactHS($d->hs_code)->first());
                $d->save();
            } else {
                echo "ERROR: for {$d->id} hs not found {$d->hs_code}\n";
            }
        }


        Schema::table('cd_detail', function (Blueprint $table) {
            // only if hs_code column still exists
            if (Schema::hasColumn('cd_detail', 'hs_code')) {
                // drop the foreign first
                $table->dropForeign('cd_detail_hs_code_foreign');
                // remove the column!
                $table->dropColumn('hs_code');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cd_detail', function (Blueprint $table) {
            // do nothing....
        });
    }
}
