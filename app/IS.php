<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IS extends Model implements IDokumen, ILinkable
{
    use TraitDokumen {
        lock as public traitLock;
        unlock as public traitUnlock;
    }
    use SoftDeletes;
    
    protected $table = 'is_header';

    public function details(){
        return $this->hasMany('App\DetailIS', 'is_header_id');
    }

    public function cd(){
        return $this->belongsTo('App\CD','cd_header_id');
    }
    
    public function lokasi(){
        return $this->belongsTo('App\Lokasi', 'lokasi_id');
    }

    //=================================================================================================
    // TRAIT OVERRIDES
    //=================================================================================================
    public function lock() {
        $cd = $this->cd;

        if ($cd->is_locked)
            return false;

        if ($this->is_locked)
            return $this->is_locked;

        return $cd->lock() && $this->traitLock();
    }

    public function unlock() {
        return $this->traitUnlock();
    }

    //=================================================================================================
    // COMPUTED PROPERTIES GO HERE!!
    //=================================================================================================
    // ambil data tahun dok dari tgl
    public function getJenisDokumenAttribute()
    {
        return 'is';
    }

    public function getSkemaPenomoranAttribute()
    {
        return 'IS/SH';
    }

    public function getLinksAttribute() {
        $links = [
            [
                'rel'   => 'self',
                'uri'   => $this->uri,
            ],
            [
                'rel'   => 'self.details',
                'uri'   => $this->uri . '/details'
            ]
        ];

        if ($this->cd) {
            $links[] = [
                'rel'   => 'cd',
                'uri'   => $this->cd->uri
            ];
        }

        return $links;
    }
}
