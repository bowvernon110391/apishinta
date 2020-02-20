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

    public function scopeByKodeNegara($query, $negara) {
        return $query->where('kode', 'like', "$negara%");
    }

    // ==================COMPUTED ATTRIBUTES=====================
    // make relation to country?
    public function getNegaraAttribute() {
        $code = $this->kode_negara;

        $negara = Negara::byExactCode($code)->first();

        return $negara;
    }

    public function getKodeNegaraAttribute() {
        return substr($this->kode, 0, 2);
    }
}
