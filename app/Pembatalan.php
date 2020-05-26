<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Pembatalan extends Model
{
    use TraitLoggable;
    use TraitDokumen;
    // automatically synchronized with 'pembatalans'

    // guarded
    protected $guarded = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // all cancellable
    public function cancellable() {
        return $this->hasMany(Cancellable::class, 'pembatalan_id', 'id');
    }

    // all sorts of dokumen that can be cancelled
    public function cd() {
        return $this->morphedByMany(CD::class, 'cancellable', 'cancellable');
    }
    public function st() {
        return $this->morphedByMany(ST::class, 'cancellable', 'cancellable');
    }
    public function spp() {
        return $this->morphedByMany(SPP::class, 'cancellable', 'cancellable');
    }
    public function spmb() {
        return $this->morphedByMany(SPMB::class, 'cancellable', 'cancellable');
    }
    public function sspcp() {
        return $this->morphedByMany(SSPCP::class, 'cancellable', 'cancellable');
    }
    public function imporSementara() {
        return $this->morphedByMany(IS::class, 'cancellable', 'cancellable');
    }
    public function bpj() {
        return $this->morphedByMany(BPJ::class, 'cancellable', 'cancellable');
    }

    // scopes
    // ===============================================================================
    public function scopeByQuery($query, $q, $from, $to) {
        return $query->where('nomor_lengkap_dok', 'LIKE', "%{$q}%")
                    ->orWhere(function ($query) use ($q) {
                        $query->pejabat($q);
                    })
                    ->when(strlen($q) >= 5 && !is_numeric($q), function ($query) use ($q) {
                        $query->orWhere( function ($query) use ($q) {
                            $query->keterangan($q);
                        } );
                    })
                    ->when($from, function ($query) use ($from) {
                        $query->from($from);
                    })
                    ->when($to, function ($query) use ($to) {
                        $query->from($to);
                    })
                    ->latest()
                    ->orderBy('tgl_dok', 'desc');
    }

    public function scopeFrom($query, $tgl) {
        return $query->where('tgl_dok', '>=', $tgl);
    }

    public function scopeTo($query, $tgl) {
        return $query->where('tgl_dok', '<=', $tgl);
    }

    public function scopePejabat($query, $namanip) {
        return $query->where('nama_pejabat', 'LIKE', "%{$namanip}%")
                    ->orWhere('nip_pejabat', 'LIKE', "%{$namanip}%");
    }

    public function scopeKeterangan($query, $keterangan) {
        return $query->where('keterangan', 'LIKE', "%{$keterangan}%");
    }

    // got the unlocked
    public function scopeUnlocked($query) {
        return $query->byLastStatusOtherThan('CLOSED');
    }
}
