<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InstruksiPemeriksaan extends Model implements IDokumen, IInspectable
{
    // just use traits
    use TraitLoggable;
    use TraitDokumen;
    use TraitInspectable;

    // enable soft deletion? 
    use SoftDeletes;

    // guarded
    protected $guarded = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // ==========================================
    // ATTRIBUTES
    // ==========================================
    public function getJenisDokumenAttribute()
    {
        return 'ip';
    }

    public function getJenisDokumenLengkapAttribute()
    {
        return 'Instruksi Pemeriksaan';
    }

    public function getSkemaPenomoranAttribute()
    {
        return 'IP';
    }

    public function getNomorLengkapAttribute()
    {
        // generate nomor lengkap...
        if (!$this->no_dok) {
            return null;
        }

        if (strlen($this->nomor_lengkap_dok) > 0) {
            return $this->nomor_lengkap_dok;
        }

        return $this->skema_penomoran . 
                '-'.
                str_pad($this->no_dok, 6, '0', STR_PAD_LEFT).
                '/SH/'.
                $this->tahun_dok;
    }
}
