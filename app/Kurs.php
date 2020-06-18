<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;

class Kurs extends Model
{
    use TraitLoggable;
    // model ini merepresentasikan data kurs
    protected $table = 'kurs';

    protected $guarded = [
        'created_at',
        'updated_at'
    ];

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

    public function scopePerTanggal($query, $tgl) {
        // tgl is assumed to be in correct format (use sqlDate)
        return $query->where('tanggal_awal', '<=', $tgl)
                        ->where('tanggal_akhir', '>=', $tgl);
    }

    public function scopePeriode($query, $tglAwal, $tglAkhir) {
        // both date assumed to be valid. USE sqlDate to convert
        return $query->where('tanggal_awal', '<=', $tglAkhir)
                        ->where('tanggal_akhir', '>=', $tglAwal);
    }

    public function scopeKode($query, $kode) {
        return $query->where('kode_valas', 'like', "%{$kode}%");
    }

    public function scopeJenis($query, $jenis) {
        return $query->where('jenis', $jenis);
    }
}
