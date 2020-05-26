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
}
