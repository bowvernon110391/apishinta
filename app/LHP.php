<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LHP extends Model implements IDokumen
{
    // implements common trait
    use TraitLoggable;
    use TraitDokumen;

    // model settings
    protected $table = 'lhp';

    protected $guarded = [
        'created_at',
        'updated_at'
    ];

    // RELATIONS
    public function inspectable() {
        return $this->morphTo();
    }

    // attribute generator
    public function getJenisDokumenAttribute() {
        return 'lhp';
    }

    public function getJenisDokumenLengkapAttribute()
    {
        return 'Laporan Hasil Pemeriksaan';
    }

    public function getSkemaPenomoranAttribute()
    {
        return 'LHP/'.$this->lokasi->nama.'/SH/';
    }
}
