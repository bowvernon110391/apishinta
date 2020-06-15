<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CD extends Model implements IDokumen, IBillable, IInspectable
{
    use TraitLoggable;
    use TraitDokumen;
    // use TraitInspectable;
    use TraitInstructable;
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
        'penumpang',
        'ndpbm'
    ];

    public function airline(){
        return $this->belongsTo('App\Airline', 'kd_airline', 'kode');
    }

    public function ndpbm(){
        return $this->belongsTo('App\Kurs', 'ndpbm_id');
    }

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
        // return $this->hasOne('App\SSPCP','cd_header_id');
        return $this->morphOne('App\SSPCP', 'billable');
    }

    public function imporSementara(){
        return $this->hasOne('App\IS','cd_header_id');
    }

    public function spmb(){
        return $this->hasOne('App\SPMB','cd_header_id');
    }

    public function spp() {
        return $this->hasOne('App\SPP', 'cd_header_id');
    }
    
    public function st() {
        return $this->hasOne('App\ST', 'cd_header_id');
    }

    // SCOPES
    // pure CD onleeeeh (not ST-ed, not SPP-ed, not Impor Sementara-ed)
    public function scopePure($query) {
        return $query->doesntHave('spp')
                    ->doesntHave('imporSementara')
                    ->doesntHave('st');
    }

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
    public static function queryScope($query, $q, $from, $to) {
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
                        ->orWhere(function ($query) use ($q) {
                            $query->byNomorLengkap($q);
                        })
                        ->latest()
                        ->orderBy('tgl_dok', 'desc');
    }

    public function scopeByQuery($query, $q='', $from=null, $to=null) {
        return CD::queryScope($query, $q, $from, $to);
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
    
    public function getJenisDokumenLengkapAttribute(){
        return 'Customs Declaration (BC 2.2)';
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

        if ($this->bpj) {
            $links[] = [
                'rel'   => 'bpj',
                'uri'   => $this->bpj->uri
            ];
        }

        if ($this->spp) {
            $links[] = [
                'rel'   => 'spp',
                'uri'   => $this->spp->uri
            ];
        }

        if ($this->st) {
            $links[] = [
                'rel'   => 'st',
                'uri'   => $this->st->uri
            ];
        }

        return $links;
    }

    // apakah importasi termasuk komersil?
    public function getKomersilAttribute() {
        return $this->declareFlags()->byName("KOMERSIL")->count() > 0;
    }

    // get package summary
    public function getPackageSummaryAttribute() {
        $package_data = [];

        foreach ($this->details as $d) {
            if (isset($package_data[$d->jenis_kemasan])) {
                // add it
                $package_data[$d->jenis_kemasan]    += $d->jumlah_kemasan;
            } else {
                // new key
                $package_data[$d->jenis_kemasan]    = $d->jumlah_kemasan;
            }
        }

        return $package_data;
    }

    // get package summary as string
    public function getPackageSummaryStringAttribute() {
        $p = $this->package_summary;

        $raw = [];

        foreach ($p as $jenis_kemasan => $jumlah_kemasan) {
            $raw[] = $jumlah_kemasan . ' ' . $jenis_kemasan;
        }

        return implode(", ", $raw);
    }

    // get summary of uraian
    public function getUraianSummaryAttribute() {
        return $this->details->map(function ($e) {
            return $e->uraian;
        });
    }

    // get first value of items
    public function getFirstValue() {
        $data_hitung = $this->simulasi_pungutan;

        // check if it has any data_perhitungan
        if (count($data_hitung['data_perhitungan']) < 1) {
            return null;
        }

        // kembaliin data pertama?
        return $data_hitung['data_perhitungan'][0];
    }

    // get total fob
    public function getTotalValue($param_name) {
        $data_hitung = $this->simulasi_pungutan;
        // return array_reduce($data_hitung['data_perhitungan'], function ($acc, $e) { return $acc + $e; }, 0);

        $fobs = array_map(function ($e) use ($param_name) { return $e[$param_name]; }, $data_hitung['data_perhitungan']);

        return array_reduce($fobs, function ($acc, $e) { return $acc+$e; }, 0);
    }

    // simulasi perhitungan
    public function getSimulasiPungutanAttribute() {
        // pertama, perhitungan BM bisa berubah tergantung
        // jenis importasi (komersil atau pribadi)?
        $isKomersil = $this->komersil;
        $pph_tarif = $this->pph_tarif;
        $ppnbm_tarif = 0;

        // $total_cukai = 0;   // for now, unaccounted. so set to 0
        
        // hitung per detil
        $total_hitung = $this
                        ->details
                        ->map(function($e) use ($isKomersil, $pph_tarif) {
                            // tentukan tarif bm
                            $tarifBm = $isKomersil ? $e->hs->bm_tarif : 10;
                            $jenisTarifBm = $e->hs->jenis_tarif;

                            // 10% utk non komersil, klo komersil ikut hs
                            $bm = $e->beaMasuk($isKomersil ? null : 10);
                            // ppn by default 10%
                            $ppn = $e->ppn($bm);
                            // pph ikut tarif yg diset di header
                            $pph = $e->pph($bm, $pph_tarif);
                            // ppnbm ikut tarif yg diset per detil
                            $ppnbm = $e->ppnbm($bm);

                            return [
                                'hs_code'       => $e->hs->kode,
                                'bm_tarif'      => (float) $tarifBm,
                                'bm_tarif_hs'   => (float) $e->hs->bm_tarif,
                                'jenis_tarif_bm'=> $jenisTarifBm,
                                'satuan_spesifik'   => $e->hs->satuan_spesifik,

                                'jumlah_satuan' => $e->jumlah_satuan,
                                'jenis_satuan'  => $e->jenis_satuan,

                                'jumlah_kemasan'    => $e->jumlah_kemasan,
                                'jenis_kemasan'     => $e->jenis_kemasan,

                                'ppn_tarif'     => 10.0,
                                'pph_tarif'     => (float) $pph_tarif,
                                'ppnbm_tarif'   => (float) $e->ppnbm_tarif,
                                'nilai_pabean'  => (float) $e->nilai_pabean,
                                'fob' => (float) $e->fob,
                                'insurance' => (float) $e->insurance,
                                'freight' => (float) $e->freight,
                                'cif' => (float) $e->cif,
                                'bm' => (float) $bm,
                                'cukai' => 0,
                                'ppn'=> (float) $ppn,
                                'pph'=> (float) $pph,
                                'ppnbm' => (float) $ppnbm,

                                'valuta' => $e->kurs->kode_valas,
                                'ndpbm'  => (float) $e->kurs->kurs_idr,

                                'long_description'  => $e->long_description,

                                'short_description' => $e->uraian,
                                'brutto'    => (float) $e->brutto
                            ];
                        });

        $hitung_total = function($acc, $e) {
            return $acc + $e;
        };

        $cari_maksimum = function($acc, $e) {
            return $acc > $e ? $acc : $e;
        };

        // untuk non komersil, BM = (total nilai pabean - pembebasan) * 10% 
        if (!$isKomersil) {
            // totalkan nilai pabean
            $nilai_pabean = $total_hitung->map(function($e) { return $e['nilai_pabean']; })->reduce($hitung_total);
            // hitung nilai pembebasan
            if (!$this->ndpbm) {
                throw new \Exception("NDPBM belum diset!");
            }

            $nilai_pembebasan = $this->pembebasan * $this->ndpbm->kurs_idr;
            $nilai_pabean -= $nilai_pembebasan;

            // gotta check if nilai_pabean < nilai_pembebasan
            if ($nilai_pabean <= 0.0) {
                throw new \Exception("Total nilai barang di bawah pembebasan. Perhitungan tidak dapat dilanjutkan", 8008);
            }

            $data_pembebasan = [
                'nilai_pembebasan'  => $this->pembebasan,
                'valuta'            => $this->ndpbm->kode_valas,
                'ndpbm'             => $this->ndpbm->kurs_idr,
                'nilai_pembebasan_rp'   => $nilai_pembebasan,
                'nilai_dasar_perhitungan'   => $nilai_pabean,
                'tarif_bm_universal'    => 10.0
            ];

            // ambil tarif ppnbm dari tarif maksimum yang diset di barang
            $ppnbm_tarif = $total_hitung->map(function ($e) { return $e['ppnbm_tarif']; })->reduce($cari_maksimum, 0);

            // hitung bm pakai nilai_pabean * 10%;
            $total_bm = ceil($nilai_pabean * 0.1 / 1000.0) * 1000.0;
            $total_cukai = 0;
            $total_ppn = ceil( ($nilai_pabean + $total_bm) * 0.1 / 1000.0 ) * 1000.0;
            $total_pph = ceil( ($nilai_pabean + $total_bm) * ($pph_tarif * 0.01) / 1000.0 ) * 1000.0;
            $total_ppnbm = ceil( ($nilai_pabean + $total_bm) * ($ppnbm_tarif * 0.01) / 1000.0 ) * 1000.0;

            // ambil kurs usd per tanggal hari ini?
        } else {
            // total dari total hitung        
            $total_bm       = $total_hitung->map(function($e) { return $e['bm']; })->reduce($hitung_total);
            $total_cukai    = $total_hitung->map(function($e) { return $e['cukai']; })->reduce($hitung_total);
            $total_ppn      = $total_hitung->map(function($e) { return $e['ppn']; })->reduce($hitung_total);
            $total_pph      = $total_hitung->map(function($e) { return $e['pph']; })->reduce($hitung_total);
            $total_ppnbm    = $total_hitung->map(function($e) { return $e['ppnbm']; })->reduce($hitung_total);
        }

        return [
            'komersil'  => $isKomersil,
            'pph_tarif' => $pph_tarif,
            'ppnbm_tarif' => $ppnbm_tarif,
            'total_bm'  => $total_bm,
            'total_cukai'  => $total_cukai,
            'total_ppn'  => $total_ppn,
            'total_pph'  => $total_pph,
            'total_ppnbm'  => $total_ppnbm,
            'total_bm_pajak'    => $total_bm+$total_cukai+$total_ppn+$total_pph+$total_ppnbm,

            'data_perhitungan'  => $total_hitung->toArray(),
            'data_pembebasan'   => $data_pembebasan ?? null
        ];
    }

    // get billing_info attribute
    public function getBillingInfoAttribute() {
        // if we have no details, return null?
        try {
            $p = $this->simulasi_pungutan;

            return [
                'jenis' => 'IMPOR',
                'tagihan'   => [
                    'total_bm'  => $p['total_bm'],
                    'total_ppn' => $p['total_ppn'],
                    'total_pph' => $p['total_pph'],
                    'total_ppnbm'   => $p['total_ppnbm'],
                    'total_denda'   => 0
                ],
                'wajib_bayar'   => [
                    'nama'  => $this->penumpang->nama,
                    'alamat'=> $this->alamat,
                    'npwp'  => strlen(trim($this->npwp)) == 15 ? $this->npwp : '',
                    'identitas' => [
                        'nomor' => $this->penumpang->no_paspor,
                        'jenis' => 'PASPOR'
                    ]
                ],
                'kurs'  => [
                    'valuta'    => $this->ndpbm->kode_valas,
                    'nilai'     => $this->ndpbm->kurs_idr
                ]
            ];
        } catch (\Exception $e) {
            throw $e;
            return null;
        }
    }
}
