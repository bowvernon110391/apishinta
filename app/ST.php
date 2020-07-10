<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ST extends AbstractDokumen implements IInstructable, INotable
{
    use TraitInstructable;
    use TraitNotable;
    // enable soft deletion
    use SoftDeletes;

    // table name
    protected $table = 'st_header';

    // default values
    protected $attributes = [
        'no_dok'    => 0,
        'jenis'     => 'KANTOR'
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

    public function pejabat() {
        return $this->belongsTo(SSOUserCache::class, 'pejabat_id', 'user_id');
    }

    // =============================================
    // TRAIT OVERRIDES
    // =============================================
    

    //=================================================================================================
    // COMPUTED PROPERTIES GO HERE!!
    //=================================================================================================
    public function getJenisDokumenAttribute() {
        return 'st';
    }
    
    public function getJenisDokumenLengkapAttribute() {
        return 'Tanda Bukti Penegahan';
    }
    public function getSkemaPenomoranAttribute()
    {
        return 'ST/' . $this->lokasi->kode . '/SH';
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

    // Helpers

    //=================================================================================================
    // SCOPES!!
    //=================================================================================================
    // scope by lokasi
    public function scopeByLokasi($query, $lokasi) {
        return $query->whereHas('lokasi', function($q) use($lokasi) {
            return $q->where('nama', 'like', "%{$lokasi}%");
        });
    }

    // scope based on CD
    public function scopeByCD($qquery, $q, $from, $to) {
        return $qquery->whereHas('cd', function ($query) use ($q, $from, $to) {
            return CD::queryScope($query, $q, $from, $to);
        });
    }

    // based on nama pejabat
    public function scopeByPejabat($query, $q) {
        // name or nip
        return $query->whereHas('pejabat', function ($qPejabat) use ($q) {
            return $qPejabat->where('name', 'like', "%$q%")
                            ->orWhere('nip', 'like', "%$q%");
        });
    }

    // tanggal dok query
    public function scopeFrom($query, $d) {
        return $query->where('tgl_dok', '>=', $d);
    }

    public function scopeTo($query, $d) {
        return $query->where('tgl_dok', '<=', $d);
    }

    // jenis
    public function scopeJenis($query, $jenis) {
        // is it array?
        // make sure it is
        if (!is_array($jenis)) {
            $jenis = [$jenis];
        }

        return $query->whereIn('jenis', $jenis);
    }

    // wildcard query
    public function scopeByQuery($query, $q='', $from=null, $to=null) {
        return $query->byLokasi($q)
                    ->orWhere(function ($query) use ($q) {
                        $query->where('no_dok', $q);
                    })
                    ->orWhere(function ($query) use ($q) {
                        $query->byPejabat($q);
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
