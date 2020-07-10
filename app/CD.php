<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CD extends AbstractDokumen implements 
IInstructable, IHasGoods, ISpecifiable, ITariffable, IHasPungutan, INotable, IPayable
{
    // use TraitInspectable;
    use TraitInstructable;

    use TraitSpecifiable;

    use TraitHasGoods;
    use TraitTariffable;
    use TraitHasPungutan;

    use TraitHasDokkaps;
    use TraitNotable;
    use TraitPayable;
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
        'no_flight' => '',
        'koli'      => 1
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

    // get jenis penerimaan (FOR BPPM)
    public function getJenisPenerimaanAttribute()
    {
        return "IMPOR";
    }

    // get NPWP (FOR BPPM)
    public function getPayerAttribute()
    {
        return [
            'nama' => $this->penumpang->nama,
            'no_identitas' => $this->penumpang->no_paspor,
            'alamat' => $this->alamat,
            'jenis_identitas' => 'PASPOR',
            'npwp' => $this->npwp
        ];
    }

    public function getPerhitunganAttribute() {
        $pungutan = CD::computePungutanCD($this->pembebasan, $this->ndpbm->kurs_idr, $this->pph_tarif, $this->detailBarang()->isPenetapan()->get());

    }

    public function getDataPembebasanAttribute() {
        if ($this->komersil) {
            return null;
        }

        return [
            'kurs_pembebasan' => (float) $this->ndpbm->kurs_idr,
            'pembebasan' => $this->pembebasan,
            'nilai_pembebasan_idr' => $this->ndpbm->kurs_idr * $this->pembebasan
        ];
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

    // ===========================DETAIL BARANG LISTEENR============================
    public function onUpdateItem(DetailBarang $d)
    {
        AppLog::logInfo("CD #{$this->id} must do something on DetailBarang #{$d->id}", $d);
        $this->validate();
    }

    public function onCreateItem(DetailBarang $d)
    {
        AppLog::logInfo("CD #{$this->id} must setup initial data on Detail Barang #{$d->id}", $d);
        $this->validate();
    }

    public function onDeleteItem(DetailBarang $d)
    {
        AppLog::logInfo("CD #{$this->id} onDelete {$d->id}", $d);
    }

    // ===========================HELPER=============================================
    public function validate() {
        // RULE #1: Personal Use CD MUST NOT HAVE OVERRIDDEN TARIFS
        if (!$this->komersil) {
            if ($this->detailBarang()->isPenetapan()->whereHas('tarif')->count()) {
                throw new \Exception("CD personal-use must not have overridden tariffs!");
            }
        }
    }

    public function computePungutanCdKomersil () {
        // grab all data barang
        if (!count($this->detailBarang)) {
            throw new \Exception("Tidak ada detail barang. Perhitungan tidak dapat dilakukan");
            return null;
        }

        // just collect each detail barang and pour into one
        /**
         * barang : [
         *  - tarif
         *  - desc
         *  - pungutan
         * ]
         * pungutan: [
         *  - per type
         * ]
         */

        $barang = $this->detailBarang->map(function ($e) {
            return [
                'tarif' => $e->valid_tarif,
                'pungutan' => $e->computePungutanImpor(),
                
                'uraian' => $e->nice_format,
                'uraian_print' => $e->print_format,
                'jumlah_jenis_kemasan' => $e->jumlah_kemasan . ' ' . $e->jenis_kemasan,
                'jumlah_jenis_satuan' => $e->jumlah_satuan . ' ' . $e->jenis_satuan,
                'hs_code' => $e->hs->kode,
                'hs_raw_code' => $e->hs->raw_code,
                'fob' => (float) $e->fob,
                'insurance' => (float) $e->insurance,
                'freight' => (float) $e->freight,
                'cif' => (float) $e->cif,
                'nilai_pabean' => $e->nilai_pabean,
                'valuta' => $e->kurs->kode_valas,
                'ndpbm' => (float) $e->kurs->kurs_idr
            ];
        });

        $pungutan_bayar = [];
        $pungutan_bebas = [];
        $pungutan_tunda = [];
        $pungutan_tanggung_pemerintah = [];

        // sum them
        foreach ($barang as $b) {
            foreach ($b['pungutan'] as $p) {
                $pungutan_bayar[$p->jenisPungutan->kode] = ($pungutan_bayar[$p->jenisPungutan->kode] ?? 0) + $p->bayar;
                $pungutan_bebas[$p->jenisPungutan->kode] = ($pungutan_bebas[$p->jenisPungutan->kode] ?? 0) + $p->bebas;
                $pungutan_tunda[$p->jenisPungutan->kode] = ($pungutan_tunda[$p->jenisPungutan->kode] ?? 0) + $p->tunda;
                $pungutan_tanggung_pemerintah[$p->jenisPungutan->kode] = ($pungutan_tanggung_pemerintah[$p->jenisPungutan->kode] ?? 0) + $p->tanggung_pemerintah;
            }
        }

        // return em?
        return [
            'barang' => $barang,
            'pungutan' => [
                'bayar' => $pungutan_bayar,
                'bebas' => $pungutan_bebas,
                'tunda' => $pungutan_tunda,
                'tanggung_pemerintah' => $pungutan_tanggung_pemerintah
            ]
        ];
    }

    public function computePungutanCdPersonal () {
        // grab all data barang
        if (!count($this->detailBarang)) {
            throw new \Exception("Tidak ada detail barang. Perhitungan tidak dapat dilakukan");
            return null;
        }

        // compute pembebasan dalam IDR
        $nilaiPembebasanIdr = $this->pembebasan * $this->ndpbm->kurs_idr;

        // extract nilai barang dalam IDR
        $nilaiBarangIdr =$this->detailBarang->map(function ($e) {
            return $e->nilai_pabean;
        });

        // jumlah total nilai barang
        $totalNilaiBarangIdr = $nilaiBarangIdr->reduce(function($acc, $e) {
            $acc += $e;
            return $acc;
        }, 0.0);

        // check value
        if ($totalNilaiBarangIdr <= $nilaiPembebasanIdr) {
            $nb = number_format($totalNilaiBarangIdr, 2);
            $np = number_format($nilaiPembebasanIdr, 2);
            throw new \Exception("Total nilai barang di bawah pembebasan (Rp {$nb} <= Rp {$np}). Perhitungan tidak dapat dilanjutkan", 333);
            return [
                'BM' => 0,
                'PPN' => 0,
                'PPh' => 0,
                'PPnBM' => 0,
                'total' => 0
            ];
        }

        // safe to continue
        $nilaiDasarIdr = $totalNilaiBarangIdr - $nilaiPembebasanIdr;

        // compute Bea masuk
        $bm = ceil($nilaiDasarIdr * 0.1 / 1000.0) * 1000.0;

        // compute nilai impor
        $nilaiImpor = $bm + $nilaiDasarIdr;

        // compute PPN and PPh Only!!
        $ppn = ceil($nilaiImpor * 0.1 / 1000.0) * 1000.0;
        $pph = ceil($nilaiImpor * ($this->pph_tarif/100.0) / 1000.0) * 1000.0;

        // extract data barang
        $barang_formatted = $this->detailBarang->map(function($e) {
            return [
                'uraian' => $e->nice_format,
                'uraian_print' => $e->print_format,
                'jumlah_jenis_kemasan' => $e->jumlah_kemasan . ' ' . $e->jenis_kemasan,
                'jumlah_jenis_satuan' => $e->jumlah_satuan . ' ' . $e->jenis_satuan,
                'hs_code' => $e->hs->kode,
                'hs_raw_code' => $e->hs->raw_code,
                'fob' => (float) $e->fob,
                'insurance' => (float) $e->insurance,
                'freight' => (float) $e->freight,
                'cif' => (float) $e->cif,
                'nilai_pabean' => $e->nilai_pabean,
                'valuta' => $e->kurs->kode_valas,
                'ndpbm' => (float) $e->kurs->kurs_idr
            ];
        });

        return [
            'pembebasan' => (float) $this->pembebasan,
            'ndpbm' => (float) $this->ndpbm->kurs_idr,
            'nilai_pembebasan_idr' => $nilaiPembebasanIdr,
            'nilai_dasar_idr' => $nilaiDasarIdr,
            'nilai_impor' => $nilaiImpor,
            'tarif_bm' => 10.0,
            'tarif_pph' => (float) $this->pph_tarif,
            'pungutan' => [
                'bm' => $bm,
                'ppn' => $ppn,
                'pph' => $pph,
            ],
            'total' => $bm + $ppn + $pph,
            'barang' => $barang_formatted->toArray()
        ];
    }

    public function syncPungutanKomersil() {
    }

    public function syncPungutanNonKomersil() {
    }
}
