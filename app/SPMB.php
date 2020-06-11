<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SPMB extends Model implements IDokumen, IInspectable
{
    use TraitLoggable;
    use TraitDokumen;
    use TraitInspectable;

    use SoftDeletes;
    
    // table setting
    protected $table = 'spmb_header';

    // default values
    protected $attributes = [
        'keterangan'    => ''
    ];

    // guarded attributes
    protected $guarded = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // default relations
    protected $with = [
        'lokasi',
        'penumpang'
    ];

    // RELATIONSHIPS
    public function details(){
        return $this->hasMany('App\DetailSPMB', 'spmb_header_id');
    }

    public function cd(){
        return $this->belongsTo('App\CD','cd_header_id');
    }
    
    public function lokasi(){
        return $this->belongsTo('App\Lokasi', 'lokasi_id');
    }

    public function penumpang(){
        return $this->belongsTo('App\Penumpang', 'penumpang_id');
    }

    public function negaraTujuan() {
        return $this->belongsTo('App\Negara', 'kd_negara_tujuan', 'kode_alpha3');
    }

    // ========================================
    // CUSTOM ATTRIBUTES!!
    // ========================================
    public function getJenisDokumenAttribute()
    {
        return 'spmb';
    }

    public function getJenisDokumenLengkapAttribute()
    {
        return 'Surat Pemberitahuan Membawa Barang (BC 3.4)';
    }

    public function getSkemaPenomoranAttribute()
    {
        return 'KB/' . $this->lokasi->nama . '/SH';
    }

    public function getLinksAttribute() {
        $links = [
            'rel'   => 'self',
            'uri'   => $this->uri
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
