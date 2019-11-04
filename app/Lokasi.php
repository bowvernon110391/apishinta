<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Lokasi extends Model
{
    //
    protected $table = 'lokasi';

    public function scopeByName($query, $lokasi) {
        return $query->where('nama', $lokasi);
    }
}
