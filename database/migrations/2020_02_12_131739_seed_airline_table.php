<?php

use App\Airline;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SeedAirlineTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $json = file_get_contents(dirname(__FILE__).'/airlines.json');

        $data = json_decode($json);

        // var_dump($data);
        // insert into database
        foreach ($data as $kode => $uraian) {
            $airline = new Airline([
                'kode'      => $kode,
                'uraian'    => $uraian
            ]);

            $airline->save();
        }
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
