<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ST extends Model
{
    use TraitLoggable;
    use TraitDokumen {
        lock as public traitLock;
        unlock as public traitUnlock;
    }
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

    public function kurs() {
        return $this->belongsTo('App\Kurs', 'kurs_id');
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
        return 'st';
    }

    public function getSkemaPenomoranAttribute()
    {
        return 'ST/' . $this->lokasi->nama . '/SH';
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
    static public function createFromCD(CD $cd) {
        if (!$cd) {
            return null;
        }

        // it's valid? must not be locked yet
        if ($cd->is_locked) {
            throw new \Exception("CD #{$cd->id} is locked already.");
        }

        // perhaps there's already st for this thing?
        if ($cd->st || $cd->spp) {
            throw new \Exception("CD #{$cd->id} is already on detention!");
        }

        // grab most recent kurs
        $first = $cd->getFirstValue();

        $data_hitung    = $cd->simulasi_pungutan;

        // compute something
        $total_fob = $cd->getTotalValue('fob');
        $total_insurance = $cd->getTotalValue('insurance');
        $total_freight = $cd->getTotalValue('freight');
        $total_cif = $total_fob + $total_insurance + $total_freight;

        $total_nilai_pabean = $cd->getTotalValue('nilai_pabean');

        $kurs = Kurs::kode($first['valuta'])
                ->perTanggal(date('Y-m-d'))
                ->first();
        
        if (!$kurs) {
            throw new \Exception("Cannot find recent data for kurs {$first['valuta']}");
        }

        $pembebasan = $data_hitung['data_pembebasan'] ? $data_hitung['data_pembebasan']['nilai_pembebasan_rp'] : 0;

        // create it
        $s = new ST([
            'tgl_dok'   => date('Y-m-d'),
            'total_fob' => $total_fob,
            'total_insurance'   => $total_insurance,
            'total_freight'     => $total_freight,
            'total_cif' => $total_cif,
            'kurs_id'   => $kurs->id,
            'keterangan'        => "",
            'pemilik_barang'    => $cd->penumpang->nama,

            'total_nilai_pabean'    => $total_nilai_pabean,
            'pembebasan'    => $pembebasan,

            'total_bm'      => $data_hitung['total_bm'],
            'total_ppn'     => $data_hitung['total_ppn'],
            'total_pph'     => $data_hitung['total_pph'],
            'total_ppnbm'   => $data_hitung['total_ppnbm'],
            'total_denda'   => 0,   // by default tidak ada denda
            'kd_negara_asal'    => $cd->pelabuhanAsal->negara->kode
        ]);

        // link to cd
        $s->cd()->associate($cd);

        // return it
        return $s;
    }

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
