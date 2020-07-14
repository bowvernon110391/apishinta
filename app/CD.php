<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;

class CD extends AbstractDokumen implements 
IInstructable, IHasGoods, ISpecifiable, ITariffable, 
IHasPungutan, INotable, IPayable, IGateable
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
    use TraitGateable;
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
        return 'CD/'. $this->lokasi->kode . '/SH';
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

    public function getDataSppbAttribute()
    {
        $total_brutto = $this->detailBarang()
                    ->isPenetapan()
                    ->get()
                    ->reduce(function($acc, $e) {
                        return $acc + $e->brutto;
                    }, 0.0);

        // ambil hawb klo ada
        $hawb = $this->dokkap()->byKode(740)->first();
        
        return [
            'pemberitahu' => [
                'npwp' => '-',
                'nama' => '-'
            ],

            'importir' => $this->payer,

            'sarana_pengangkut' => $this->airline->uraian,
            'no_flight' => $this->no_flight,
            'jumlah_jenis_kemasan' => $this->koli . ' Koli',
            'brutto' => $total_brutto,

            'no_bc11' => '-',
            'tgl_bc11' => '-',
            'pos_bc11' => '-',
            'subpos_bc11' => '-',
            'subsubpos_bc11' => '-',

            'hawb' => [
                'nomor' => $hawb->nomor_lengkap_dok ?? '-',
                'tanggal' => $hawb->tgl_dok ?? '-'
            ]
        ];
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
