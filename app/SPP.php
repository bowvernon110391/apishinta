<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SPP extends Model
{
    use TraitLoggable;
    use TraitDokumen {
        lock as public traitLock;
        unlock as public traitUnlock;
    }
    // enable soft deletion
    use SoftDeletes;

    // table name
    protected $table = 'spp_header';

    // default values
    protected $attributes = [
        'no_dok'    => 0,
    ];

    // fillables/guarded
    protected $guarded = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // =============================================
    // RELATIONS
    // =============================================
    public function cd() {
        return $this->belongsTo('App\CD', 'cd_header_id');
    }

    public function negara() {
        return $this->belongsTo('App\Negara', 'kd_negara_asal', 'kode');
    }

    public function lokasi(){
        return $this->belongsTo('App\Lokasi', 'lokasi_id');
    }

    // =============================================
    // TRAIT OVERRIDES
    // =============================================
    public function lock() {
        $cd = $this->cd;

        if (!$cd) {
            return false;
        }

        try {
            $cd->lock();
            return $this->traitLock();
        } catch (\Exception $e) {
            //throw $th;
            $this->unlock();
            return false;
        }
    }

    public function unlock() {
        return $this->traitUnlock();
    }

    //=================================================================================================
    // COMPUTED PROPERTIES GO HERE!!
    //=================================================================================================
    public function getJenisDokumenAttribute() {
        return 'spp';
    }

    public function getSkemaPenomoranAttribute()
    {
        return 'SPP/' . $this->lokasi->nama . '/SH';
    }

    public function getLinksAttribute() {
        $links = [
            [
                'rel'   => 'self',
                'uri'   => $this->uri,
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