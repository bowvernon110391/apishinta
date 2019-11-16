<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReferensiJenisDetailSekunder extends Model
{
    protected $table = 'referensi_jenis_detail_sekunder';

    // gotta grab some by ids? nope by name
    public function scopeByName($query, $name) {
        return $query->where('nama', $name);
    }
}
