<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tarif extends Model
{
    // settings
    protected $table = 'tarif';

    protected $guarded = [
        'created_at',
        'updated_at'
    ];

    protected $attributes = [
        'bayar' => 100.0,
        'bebas' => 0.0,
        'tunda' => 0.0,
        'tanggung_pemerintah' => 0.0,
        'overridable' => true
    ];

    protected $with = [
        'jenisPungutan'
    ];

    // Relations
    public function tariffable() {
        return $this->morphTo();
    }

    public function jenisPungutan() {
        return $this->belongsTo(ReferensiJenisPungutan::class, 'jenis_pungutan_id');
    }

    // Attributes
    public function getIsPenetapanAttribute() {
        return $this->tariffable && $this->tariffable->is_penetapan;
    }

    // Scopes
    public function scopeByKode($query, $kode) {
        return $query->whereHas('jenisPungutan', function ($query) use ($kode) {
            $query->byKode($kode);
        });
    }

    public function scopeOverridable($query) {
        return $query->where('overridable', true);
    }
}
