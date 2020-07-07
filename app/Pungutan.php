<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pungutan extends Model implements ISpecifiable
{
    use TraitSpecifiable;
    
    // settings
    protected $table = 'pungutan';

    protected $guarded = [
        'created_at',
        'updated_at'
    ];

    // default values
    protected $attributes = [
        'bayar' => 0,
        'bebas' => 0,
        'tunda' => 0,
        'tanggung_pemerintah' => 0
    ];

    // RELATIONS
    public function dutiable() {
        return $this->morphTo();
    }

    public function jenisPungutan() {
        return $this->belongsTo(ReferensiJenisPungutan::class, 'jenis_pungutan_id');
    }

    public function pejabat() {
        return $this->belongsTo(SSOUserCache::class, 'pejabat_id');
    }

    // SCOPES
    public function scopePenetapan($query) {
        return $query->whereNotNull('pejabat_id');
    }

    public function scopePengajuan($query) {
        return $query->whereNull('pejabat_id');
    }

    public function scopeByKode($query, $kode) {
        return $query->whereHas('jenisPungutan', function ($query) use ($kode) {
            $query->byKode($kode);
        });
    }
}
