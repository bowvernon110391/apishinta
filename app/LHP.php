<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LHP extends AbstractDokumen
{
    // model settings
    protected $table = 'lhp';

    protected $guarded = [
        'created_at',
        'updated_at'
    ];

    // default attrib
    protected $attributes = [
        'isi' => '',
        'lokasi_id' => 3
    ];

    // RELATIONS
    public function inspectable() {
        return $this->morphTo();
    }

    public function pemeriksa() {
        return $this->belongsTo(SSOUserCache::class, 'pemeriksa_id');
    }

    public function lokasi() {
        return $this->belongsTo(Lokasi::class, 'lokasi_id');
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
        return 'LHP';
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

    public function getWaktuMulaiAttribute() {
        return timePart($this->waktu_mulai_periksa);
    }

    public function getTanggalMulaiAttribute() {
        return datePart($this->waktu_mulai_periksa);
    }

    public function getWaktuSelesaiAttribute() {
        return timePart($this->waktu_selesai_periksa);
    }

    public function getTanggalSelesaiAttribute() {
        return datePart($this->waktu_selesai_periksa);
    }

    public function getInstructableAttribute() {
        $ip = $this->inspectable;
        if (get_class($ip) == InstruksiPemeriksaan::class) {
            // yep, return the real one
            return $ip->instructable;
        }
        return null;
    }
}
