<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Negara extends Model
{
    use TraitLoggable;

    protected $table = 'referensi_negara';
    public $timestamps = false;

    public function scopeByCode($query, $code) {
        return $query->where('kode', 'LIKE', "%{$code}%");
    }

    public function scopeByCode3($query, $code) {
        return $query->where('kode_alpha3', 'LIKE', "%{$code}%");
    }

    public function scopeByExactCode($query, $code) {
        return $query->where('kode', '=', $code);
    }

    public function scopeByExactCode3($query, $code) {
        return $query->where('kode_alpha3', '=', $code);
    }

    public function scopeByUraian($query, $uraian) {
        return $query->where('uraian', 'LIKE', "%{$uraian}%");
    }

    public function penumpang() {
        return $this->hasMany('App\Penumpang', 'kebangsaan', 'kode');
    }
}
