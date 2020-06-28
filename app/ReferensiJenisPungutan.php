<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReferensiJenisPungutan extends Model
{
    // settings
    protected $table = 'referensi_jenis_pungutan';

    protected $guarded = [
        'created_at',
        'updated_at'
    ];

    public function scopeByKode($query, $kode) {
        return $query->where('kode', $kode);
    }
}
