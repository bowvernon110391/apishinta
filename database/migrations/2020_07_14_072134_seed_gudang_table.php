<?php

use App\Gudang;
use App\TPS;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SeedGudangTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gudang', function (Blueprint $table) {
            $fp = fopen(__DIR__.'/gudang.csv', 'r');
            $count = 0;
            // now skip one?
            while ($data = fgetcsv($fp)) {
                if (++$count > 1) {
                    // skip first fow
                    $p = new Gudang([
                        'kode' => $data[0],
                        'tps_id' => TPS::byKode($data[1])->first()->id ?? null
                    ]);

                    $p->save();
                }
            }
            fclose($fp);

            echo "Inserted {$count} Gudang.\n";
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('gudang', function (Blueprint $table) {
            //
        });
    }
}
