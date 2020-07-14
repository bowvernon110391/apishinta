<?php

use App\PJT;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SeedPjtTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pjt', function (Blueprint $table) {
            $fp = fopen(__DIR__.'/pjt.csv', 'r');
            $count = 0;
            // now skip one?
            while ($data = fgetcsv($fp)) {
                if (++$count > 1) {
                    // skip first fow
                    $p = new PJT([
                        'npwp' => $data[0],
                        'nama' => $data[1],
                        'alamat' => $data[2] ?? null
                    ]);

                    $p->save();
                }
            }
            fclose($fp);

            echo "Inserted {$count} PJT data.\n";
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pjt', function (Blueprint $table) {
            //
        });
    }
}
