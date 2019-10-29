<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SeedReferensiNegaraKemasanSatuan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // $rawSql = file_get_contents(dirname(__FILE__) . '/ref_negara_kemasan_satuan.sql');

        // Seed table referensi_negara
        $fNegara = fopen(dirname(__FILE__).'/ref_negara.csv', "r");
        while ( ($csvNegara = fgetcsv($fNegara)) ) {
            // var_dump($csvNegara);

            if ($csvNegara[0] =='ID') 
                continue;

            DB::table('referensi_negara')->insert([
                'id'        => $csvNegara[0],
                'kode'      => $csvNegara[1],
                'uraian'    => $csvNegara[2]
            ]);
        }
        
        fclose($fNegara);

        // Seed table referensi_kemasan
        $fKemasan = fopen(dirname(__FILE__).'/ref_kemasan.csv', "r");
        while ( ($csvKemasan = fgetcsv($fKemasan)) ) {
            // var_dump($csvNegara);

            if ($csvKemasan[0] =='ID') 
                continue;

            DB::table('referensi_kemasan')->insert([
                'id'     => $csvKemasan[0],
                'kode'   => $csvKemasan[1],
                'uraian' => $csvKemasan[2]
            ]);
        }
        
        fclose($fKemasan);

        // Seed table referensi_satuan
        $fSatuan = fopen(dirname(__FILE__).'/ref_satuan.csv', "r");
        while ( ($csvSatuan = fgetcsv($fSatuan)) ) {
            // var_dump($csvNegara);

            if ($csvSatuan[0] =='ID') 
                continue;

            DB::table('referensi_satuan')->insert([
                'id'     => $csvSatuan[0],
                'kode'   => $csvSatuan[1],
                'uraian' => $csvSatuan[2]
            ]);
        }
        
        fclose($fSatuan);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        DB::table('referensi_negara')->truncate();
        DB::table('referensi_kemasan')->truncate();
        DB::table('referensi_satuan')->truncate();    
    }
}
