<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReferensiJenisDokkap extends Model
{
    // table name
    protected $table = 'referensi_jenis_dokkap';

    protected $guarded = [
        'created_at',
        'updated_at'
    ];

    // gotta grab by some name or id
    public function scopeByCode($query, $kode) {
        return $query->where('id', $kode);
    }

    public function scopeByName($query, $name) {
        return $query->where('nama', 'like', "%{$name}%");
    }
}
