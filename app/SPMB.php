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
        return $this->belongsTo('App\Negara', 'kd_negara_tujuan', 'kode');
    }

    public function airline() {
        return $this->belongsTo('App\Airline', 'kd_flight_berangkat', 'kode');
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

    //=================================================================================================
    // SCOPES!!
    //=================================================================================================
    // scope by lokasi
    public function scopeByLokasi($query, $lokasi) {
        return $query->whereHas('lokasi', function ($q) use($lokasi) {
            return $q->where('nama', 'like', "%{$lokasi}%");
        });
    }

    // scope by cd
    public function scopeByCD($query, $q, $from, $to) {
        return $query->whereHas('cd', function ($qq) use ($q, $from, $to) {
            return CD::queryScope($qq, $q, $from, $to);
        });
    }

    // based on pejabat?
    public function scopeByPejabat($query, $q) {
        return $query->where('nama_pejabat', 'like', "%{$q}%")
                    ->orWhere('nip_pejabat', 'like', "%{$q}%");
    }

    // tanggal dok query
    public function scopeFrom($query, $d) {
        return $query->where('tgl_dok', '>=', $d);
    }

    public function scopeTo($query, $d) {
        return $query->where('tgl_dok', '<=', $d);
    }

    // by penumpang
    public function scopeByPenumpang($query, $penumpang) {
        return $query->whereHas('penumpang', function ($query) use ($penumpang) {
            return $query->where('nama', 'like', "%{$penumpang}%");
        });
    }

    // wildcard query
    public function scopeByQuery($query, $q, $from=null, $to=null) {
        return $query->byLokasi($q)
                    ->orWhere(function ($query) use ($q) {
                        $query->where('no_dok', $q);
                    })
                    ->orWhere(function ($query) use ($q) {
                        $query->byPejabat($q);
                    })
                    ->orWhere(function ($query) use ($q) {
                        $query->byPenumpang($q);
                    })
                    ->orWhere(function ($query) use ($q, $from, $to) {
                        $query->byCD($q, $from, $to);
                    })
                    ->orWhere(function ($query) use ($q) {
                        $query->byNomorLengkap($q);
                    })
                    ->when($from, function ($query) use ($from) {
                        $query->from($from);
                    })
                    ->when($to, function ($query) use ($to) {
                        $query->to($to);
                    })
                    ->latest()
                    ->orderBy('tgl_dok', 'desc');
    }
}
