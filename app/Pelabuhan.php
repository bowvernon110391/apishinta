<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pelabuhan extends Model
{
    // settings
    protected $table = 'pelabuhan';
    public $timestamps = false;

    public function scopeByKode($query, $kode) {
        return $query->where('kode', 'like', "%$kode%");
    }

    public function scopeByNama($query, $nama) {
        return $query->where('nama', 'like', "%$nama%");
    }
}
