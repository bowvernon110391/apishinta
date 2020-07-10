<?php

namespace App;

class SPPB extends AbstractDokumen
{
    // settings
    protected $table = 'sppb';

    protected $guarded = [
        'created_at',
        'updated_at'
    ];

    // relations
    public function gateable() {
        return $this->morphTo();
    }

    public function lokasi() {
        return $this->morphTo();
    }

    public function pejabat() {
        return $this->belongsTo(SSOUserCache::class, 'pejabat_id', 'user_id');
    }

    // attribs
    public function getJenisDokumenAttribute()
    {
        return 'sppb';
    }

    public function getJenisDokumenLengkapAttribute()
    {
        return 'Surat Persetujuan Pengeluaran Barang';
    }

    public function getSkemaPenomoranAttribute()
    {
        return 'KPU.03';
    }
}
