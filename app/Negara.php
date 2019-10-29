<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Negara extends Model
{
    protected $table = 'referensi_negara';
    public $timestamps = false;

    public function scopeByCode($query, $code) {
        return $query->where('kode', 'LIKE', "%{$code}%");
    }

    public function scopeByExactCode($query, $code) {
        return $query->where('kode', '=', $code);
    }

    public function scopeByUraian($query, $uraian) {
        return $query->where('uraian', 'LIKE', "%{$uraian}%");
    }

    public function penumpang() {
        return $this->hasMany('App\Penumpang', 'kebangsaan', 'kode');
    }
}
