<?php

use App\Airline;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateAirlineTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // update data
        $fp = fopen(__DIR__.'/maskapai.csv', 'r');

        // do it?
        DB::beginTransaction();

        try {
            $inserted = 0;
            $updated = 0;
            // update data and later commit?
            while($data = fgetcsv($fp)) {
                $kode = trim($data[0]);
                $def = trim($data[1]);
                $airline = Airline::byExactCode($kode)->first();

                // echo "$kode : $def\n";

                // if there exist one, update
                if ($airline) {
                    // update existing
                    echo "Update [{$kode}] => {$def}\n";
                    $airline->uraian = $def;
                    $airline->save();

                    ++$updated;
                } else {
                    // make new entry
                    echo "Insert [{$kode}] => {$def}\n";
                    $airline = new Airline([
                        'kode' => $kode,
                        'uraian' => $def
                    ]);
                    $airline->save();

                    ++$inserted;
                }
            }

            // Commit
            DB::commit();

            echo "Airline: Updated({$updated}), Inserted({$inserted})\n";
        } catch (\Throwable $e) {
            DB::rollBack();
            fclose($fp);
            return;
        }

        fclose($fp);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // no rollback, sadly
    }
}
