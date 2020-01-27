<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class SeedReferensiJenisDokkapTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // read the csv
        $fJenisDokkap = fopen(dirname(__FILE__).'/ref_jenis_dokkap.csv', 'r');
        while ( ($csv = fgetcsv($fJenisDokkap)) ) {
            if (strtoupper($csv[0]) == 'ID')
                continue;

            DB::table('referensi_jenis_dokkap')->insert([
                'id'    => $csv[0],
                'nama'  => $csv[1],
                'usable'=> $csv[2]
            ]);
        }
        fclose($fJenisDokkap);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('referensi_jenis_dokkap')->truncate();
    }
}
