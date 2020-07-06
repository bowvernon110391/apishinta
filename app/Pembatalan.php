<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Pembatalan extends AbstractDokumen
{
    // automatically synchronized with 'pembatalans'

    // guarded
    protected $guarded = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // shared between instances (global too)
    // list of cancellable document
    protected static $supported = [
        'cd'    => CD::class,
        'is'    => IS::class,
        'bpj'   => BPJ::class,
        'st'    => ST::class,
        'spp'   => SPP::class
    ];

    // helper to instantiate class
    public static function instantiate($classname, $id) {
        if (!array_key_exists($classname, Pembatalan::$supported)) {
            return null;
        }

        // it's supported. return it
        return call_user_func(Pembatalan::$supported[$classname]."::find", $id);
    }

    // helper to return qualified class name
    public static function getSupportedClassname($classname) {
        if (!array_key_exists($classname, Pembatalan::$supported)) {
            if (!class_exists($classname)) {
                return null;
            } else {
                return $classname;
            }
        }

        return Pembatalan::$supported[$classname];
    }

    

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

    // by status
    public function scopeByStatus($query, $status) {
        $status = strtoupper($status);

        if ($status == 'LOCKED') {
            return $query->locked();
        } else if ($status == 'UNLOCKED') {
            return $query->unlocked();
        } 

        return $query;
    }

    // got the unlocked
    public function scopeUnlocked($query) {
        return $query->byLastStatusOtherThan('CLOSED');
    }

    // got the locked
    public function scopeLocked($query) {
        return $query->byLastStatus('CLOSED');
    }
}
