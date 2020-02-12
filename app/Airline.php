<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Airline extends Model
{
    protected $table = 'airline';

    protected $guarded = [
        'created_at',
        'updated_at'
    ];

    public function scopeByExactCode($query, $kode) {
        return $query->where('kode', $kode);
    }
}
