<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CD extends Model implements IDokumen
{
    use TraitLoggable;
    use TraitDokumen;
    // enable soft Deletion
    use SoftDeletes;


    // table name
    protected $table = 'cd_header';

    // default values
    protected $attributes = [
        'no_dok'    => 0,   // 0 berarti blom dinomorin
        'npwp'      => '-',
        'nib'       => '-',
        'alamat'    => '',
        'no_flight' => ''
    ];

    // fillables
    protected $guarded = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // always loaded relations
    protected $with = [
        'lokasi',
        'declareFlags',
        'penumpang'
    ];

    public function details(){
        return $this->hasMany('App\DetailCD', 'cd_header_id');
    }

    public function lokasi(){
        return $this->belongsTo('App\Lokasi', 'lokasi_id');
    }

    public function pelabuhanAsal() {
        return $this->belongsTo('App\Pelabuhan', 'kd_pelabuhan_asal', 'kode');
    }

    public function pelabuhanTujuan() {
        return $this->belongsTo('App\Pelabuhan', 'kd_pelabuhan_tujuan', 'kode');
    }

    public function declareFlags(){
        return $this->belongsToMany('App\DeclareFlag', 'cd_header_declare_flag', 'cd_header_id', 'declare_flag_id');
    }

    public function penumpang(){
        return $this->belongsTo('App\Penumpang', 'penumpang_id');
    }

    public function sspcp(){
        return $this->hasOne('App\SSPCP','cd_header_id');
    }

    public function imporSementara(){
        return $this->hasOne('App\IS','cd_header_id');
    }

    public function spmb(){
        return $this->hasOne('App\SPMB','cd_header_id');
    }

    // SCOPES
    // scope by number
    public function scopeNo($query, $no) {
        return $query->where('no', $no);
    }


    // scope from (tanggal dok)
    public function scopeFrom($query, $tgl) {
        return $query->where('tgl_dok', '>=', $tgl);
    }

    // scope to (tanggal dok)
    public function scopeTo($query, $tgl) {
        return $query->where('tgl_dok', '<=', $tgl);
    }

    // scope by penumpang (LIKE penumpang name)    
    public function scopeByPenumpang($query, $nama) {
        return $query->whereHas('penumpang', function($q) use($nama) {
            $qString = "%{$nama}%";
            return $q->where('nama', 'like', $qString)
                    ->orWhere('pekerjaan', 'like', $qString)
                    ->orWhere('kebangsaan', 'like', $qString)
                    ->orWhere('no_paspor', 'like', $qString);
        });
    }

    // scope by lokasi
    public function scopeByLokasi($query, $lokasi) {
        return $query->whereHas('lokasi', function($q) use($lokasi) {
            return $q->where('nama', 'like', "%{$lokasi}%");
        });
    }

    // scope by declare flags
    public function scopeByDeclareFlags($query, $flags) {
        // if not array, convert it
        return $query->whereHas('declareFlags', function($q) use ($flags) {
            return $q->byName($flags);
        });
    }

    // scope by q (WILD QUERY)
    public function scopeByQuery($query, $q='', $from=null, $to=null) {
        return $query->where('npwp', 'like', "%{$q}%")
                        ->orWhere('nib', 'like', "%{$q}%")
                        ->orWhere('alamat', 'like', "%{$q}%")
                        ->orWhere('no_flight', 'like', "%{$q}%")
                        ->orWhere('no_dok', $q)
                        ->orWhere(function ($query) use ($q) {
                            $query->byLokasi($q);
                        })
                        ->orWhere(function ($query) use ($q) {
                            $query->byPenumpang($q);
                        })
                        ->orWhere(function ($query) use ($q) {
                            $query->byDeclareFlags($q);
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

    // extrak data declareflags dalam bentuk flat array
    public function getFlatDeclareFlagsAttribute() {
        $flags = [];

        foreach ($this->declareFlags as $df) {
            $flags[] = $df->nama;
        }

        return $flags;
    }

    public function getJenisDokumenAttribute(){
        return 'cd';
    }

    public function getSkemaPenomoranAttribute(){
        return 'CD/'. $this->lokasi->nama . '/SH';
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
            ],
            [
                'rel'   => 'penumpang',
                'uri'   => $this->penumpang->uri
            ]
        ];

        if ($this->imporSementara) {
            $links[] = [
                'rel'   => 'is',
                'uri'   => $this->imporSementara->uri
            ];
        }

        if ($this->sspcp) {
            $links[] = [
                'rel'   => 'sspcp',
                'uri'   => $this->sspcp->uri
            ];
        }

        return $links;
    }

    // apakah importasi termasuk komersil?
    public function getKomersilAttribute() {
        return $this->declareFlags()->byName("KOMERSIL")->count() > 0;
    }

    // simulasi perhitungan
    public function getSimulasiPungutanAttribute() {
        // pertama, perhitungan BM bisa berubah tergantung
        // jenis importasi (komersil atau pribadi)?
        $isKomersil = $this->komersil;
        $pph_tarif = $this->pph_tarif;

        // $total_cukai = 0;   // for now, unaccounted. so set to 0
        
        // hitung per detil
        $total_hitung = $this
                        ->details
                        ->map(function($e) use ($isKomersil, $pph_tarif) {
                            // 10% utk non komersil, klo komersil ikut hs
                            $bm = $e->beaMasuk($isKomersil ? null : 10);
                            // ppn by default 10%
                            $ppn = $e->ppn($bm);
                            // pph ikut tarif yg diset di header
                            $pph = $e->pph($bm, $pph_tarif);
                            // ppnbm ikut tarif yg diset per detil
                            $ppnbm = $e->ppnbm($bm);

                            return [
                                'bm' => $bm,
                                'cukai' => 0,
                                'ppn'=> $ppn,
                                'pph'=> $pph,
                                'ppnbm' => $ppnbm
                            ];
                        });

        $hitung_total = function($acc, $e) {
            return $acc + $e;
        };
        // total dari total hitung
        $total_bm       = $total_hitung->map(function($e) { return $e['bm']; })->reduce($hitung_total);
        $total_cukai    = $total_hitung->map(function($e) { return $e['cukai']; })->reduce($hitung_total);
        $total_ppn      = $total_hitung->map(function($e) { return $e['ppn']; })->reduce($hitung_total);
        $total_pph      = $total_hitung->map(function($e) { return $e['pph']; })->reduce($hitung_total);
        $total_ppnbm    = $total_hitung->map(function($e) { return $e['ppnbm']; })->reduce($hitung_total);

        return [
            'komersil'  => $isKomersil,
            'pph_tarif' => $pph_tarif,
            'total_bm'  => $total_bm,
            'total_cukai'  => $total_cukai,
            'total_ppn'  => $total_ppn,
            'total_pph'  => $total_pph,
            'total_ppnbm'  => $total_ppnbm,
            'total_bm_pajak'    => $total_bm+$total_cukai+$total_ppn+$total_pph+$total_ppnbm
        ];
    }
}
