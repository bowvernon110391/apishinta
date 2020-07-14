<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TPS extends Model
{
    // setting
    protected $table = 'tps';

    protected $guarded = [
        'created_at',
        'updated_at'
    ];

    // relations
    public function gudang() {
        return $this->hasMany(Gudang::class, 'tps_id');
    }

    // scopes
    public function scopeByKode($query, $kode) {
        return $query->where('kode', $kode);
    }
}
