<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;

class Kurs extends Model
{
    // model ini merepresentasikan data kurs
    protected $table = 'kurs';

    // cari kurs valid di tanggal tersebut. Date in Y-m-d
    public static function findValidKursOnDateOrFail($date) {
        // if date is not in valid format, reject
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            // date is not in Y-m-d
            throw new InvalidArgumentException("Invalid date format for '{$date}'. Use Y-m-d please");
        }
        
        $result = Kurs::where('tanggal_awal', '<=', $date)
                    ->where('tanggal_akhir', '>=', $date)
                    ->get();

        // throw fail if set
        if (count($result) === 0) {
            throw new ModelNotFoundException("Cannot find valid kurs on date: {$date}");
        }

        return $result;
    }
}
