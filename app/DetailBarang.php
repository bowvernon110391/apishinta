<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;

class DetailBarang extends Model implements ISpecifiable, ITariffable
{
    use SoftDeletes;
    use TraitSpecifiable;
    use TraitTariffable;
    use TraitLoggable;

    // settings
    protected $table = 'detail_barang';

    protected $guarded = [
        'created_at',
        'updated_at'
    ];

    protected $attributes = [
        'uraian' => '',
        'jumlah_kemasan' => 0,
        'jenis_kemasan' => 'PK',
        'fob' => 0.0,
        'insurance' => 0.0,
        'freight' => 0.0,
        'brutto' => 0.0
    ];

    // RELATIONSHIP
    public function header() {
        return $this->morphTo();
    }

    public function kurs() {
        return $this->belongsTo(Kurs::class, 'kurs_id');
    }

    public function hs() {
        return $this->belongsTo(HsCode::class, 'hs_id');
    }

    public function jenisKemasan() {
        return $this->belongsTo(Kemasan::class, 'jenis_kemasan', 'kode');
    }

    public function jenisSatuan() {
        return $this->belongsTo(Satuan::class, 'jenis_satuan', 'kode');
    }

    public function kategori() {
        return $this->belongsToMany(Kategori::class, 'detail_barang_kategori', 'detail_barang_id', 'kategori_id')->withTimestamps();
    }

    public function detailSekunder() {
        return $this->hasMany(DetailSekunder::class, 'detail_barang_id');
    }

    public function fasilitas() {
        return $this->hasMany(Fasilitas::class, 'detail_barang_id');
    }

    // ATTRIBUTES!!!

    public function getKategoriTagsAttribute() {
        return $this->kategori->map(function ($e) { return $e->nama; })->toArray();
    }

    public function getCifAttribute() {
        return (float) ($this->fob + $this->insurance + $this->freight);
    }

    public function getNilaiPabeanAttribute() {
        return (float) $this->kurs->kurs_idr * $this->cif;
    }

    public function getTarifArrayAttribute() {
        // grab all tariffs.
        // 1st, grab basic tariffs from hs
        $tarif = [
            'BM' => [
                'tarif' => (float) $this->hs->bm_tarif,
                'jenis' => $this->hs->jenis_tarif
            ],
            'PPN' => [
                'tarif' => (float) $this->hs->ppn_tarif ?? 10.0
            ],
            'PPnBM' => [
                'tarif' => (float) $this->hs->ppnbm_tarif
            ]
        ];

        // bm follows header if it's CD
        if ($this->header && get_class($this->header) == CD::class) {
            if (!$this->header->komersil) {
                $tarif['BM'] = [
                    'tarif' => 10.0,
                    'jenis' => 'ADVALORUM'
                ];

                // if there's PPnBM, remove it?
                if (array_key_exists('PPnBM', $tarif)) {
                    unset($tarif['PPnBM']);
                }
            }
        }

        // pph follows header
        if ($this->header && ($this->header->pph_tarif ?? $this->header->tarif_pph) ) {
            $tarif['PPh'] = [
                'tarif' => (float) $this->header->pph_tarif ?? $this->header->tarif_pph
            ];
        }

        // read all our tariffs entry, and replace accordingly?
        foreach ($this->tarif as $t) {
            $tarif[$t->jenisPungutan->kode] = [
                'tarif' => (float) $t->tarif,
                'jenis' => $t->jenis,
                'bayar' => (float) $t->bayar,
                'bebas' => (float) $t->bebas,
                'tunda' => (float) $t->tunda,
                'tanggung_pemerintah' => (float) $t->tanggung_pemerintah,
            ];
        }

        // kalau ada keringanan, override pakek tarif keringanan jadinya
        foreach ($this->fasilitas as $f) {
            // kalau jenisnya keringanan saja
            if ($f->jenis == 'KERINGANAN') {
                // kalau BM, paksa jadi ADVALORUM
                if ($f->jenisPungutan->kode == 'BM') {
                    $tarif['BM']['jenis'] = 'ADVALORUM';
                }
                // ambil tarif terendah
                $tarif[$f->jenisPungutan->kode]['tarif'] = (float) min((float) $tarif[$f->jenisPungutan->kode]['tarif'], (float) $f->tarif_keringanan);
            } else {
                // jenis PEMBEBASAN ataupun TIDAK_DIPUNGUT, maka set nilai bebasnya
                $tarif[$f->jenisPungutan->kode]['bebas'] = 100.0;   // pembebasan 100%
                $tarif[$f->jenisPungutan->kode]['bayar'] = 0.0;     // tidak ada yg dbayar
            }
        }

        // return for now
        return $tarif;
    }

    public function getValidTarifAttribute() {
        return array_filter($this->tarif_array, function ($e, $k) {
            // only pass those with nonzero tarif OR if IT's BM
            //return $e['tarif'] > 0.0 || substr($k, 0, 2) == "BM";
            return true;
        }, ARRAY_FILTER_USE_BOTH);
    }

    public function computePungutanImpor() {
        // spawn a collection of Pungutan here
        $valid_tarif = $this->valid_tarif;

        // filter all bm tarif
        $tarif_bm = array_filter($valid_tarif, function ($e) {
            return substr($e, 0, 2) == 'BM';
        }, ARRAY_FILTER_USE_KEY);

        // filter all pajak tarif
        $tarif_pajak = array_filter($valid_tarif, function ($e) {
            return substr($e, 0, 2) == 'PP';
        }, ARRAY_FILTER_USE_KEY);

        // PUNGUTAN
        $pungutan = [];

        // #1 hitung BEA MASUK dan TOTALNYA
        $total_bm = 0;

        foreach ($tarif_bm as $kode => $tbm) {
            if ($tbm['jenis'] == 'SPESIFIK') {
                // hitung metode spesifik
                $bm = (float) $this->jumlah_satuan * $tbm['tarif'];
            } else {
                // hitung metode persentase
                $bm = ceil($this->nilai_pabean * $tbm['tarif'] * 0.01 / 1000.0) * 1000.0;
            }


            // compute bayar, bebas, tunda, tanggung_pemerintah
            $bm_bayar = round($bm * ($tbm['bayar'] ?? 100.0) * 0.01, -3);
            $bm_bebas = round($bm * ($tbm['bebas'] ?? 0) * 0.01, -3);
            $bm_tunda = round($bm * ($tbm['tunda'] ?? 0) * 0.01, -3);
            $bm_tanggung_pemerintah = round($bm * ($tbm['tanggung_pemerintah'] ?? 0) * 0.01, -3);

            // accumulate first
            $total_bm += $bm_bayar;

            // spawn new pungutan
            $p = new Pungutan([
                'bayar' => $bm_bayar,
                'bebas' => $bm_bebas,
                'tunda' => $bm_tunda,
                'tanggung_pemerintah' => $bm_tanggung_pemerintah,
            ]);

            $p->jenisPungutan()->associate(ReferensiJenisPungutan::byKode($kode)->first());

            $pungutan[] = $p;
        }

        // #2 HITUNG PAJAK2nya
        $nilai_impor = $total_bm + $this->nilai_pabean;

        foreach ($tarif_pajak as $kode => $tp) {
            // hitung pajak
            $pajak = ceil($nilai_impor * $tp['tarif'] * 0.01 / 1000.0) * 1000.0;

            $bayar = round($pajak * ($tp['bayar'] ?? 100.0) * 0.01, -3);
            $bebas = round($pajak * ($tp['bebas'] ?? 0) * 0.01, -3);
            $tunda = round($pajak * ($tp['tunda'] ?? 0) * 0.01, -3);
            $tanggung_pemerintah = round($pajak * ($tp['tanggung_pemerintah'] ?? 0) * 0.01, -3);

            $p = new Pungutan([
                'bayar' => $bayar,
                'bebas' => $bebas,
                'tunda' => $tunda,
                'tanggung_pemerintah' => $tanggung_pemerintah,
            ]);
            $p->jenisPungutan()->associate(ReferensiJenisPungutan::byKode($kode)->first());

            $pungutan[] = $p;
        }

        return $pungutan;
    }

    // SKEMA: PEMBEBASAN PER BARANG
    public function computePungutanImporWithPembebasan() {
        // spawn a collection of Pungutan here
        $valid_tarif = $this->valid_tarif;

        // filter all bm tarif
        $tarif_bm = array_filter($valid_tarif, function ($e) {
            return substr($e, 0, 2) == 'BM';
        }, ARRAY_FILTER_USE_KEY);

        // filter all pajak tarif
        $tarif_pajak = array_filter($valid_tarif, function ($e) {
            return substr($e, 0, 2) == 'PP';
        }, ARRAY_FILTER_USE_KEY);

        // PUNGUTAN
        $pungutan = [];

        // #1 hitung BEA MASUK dan TOTALNYA
        $total_bm = 0;

        // compute nilai dasar
        if (!$this->header) {
            throw new \Exception("Cannot compute nilai pungutan: DetailBarang #{$this->id} has no Header!");
        }

        $nilai_pembebasan_idr = (float) ($this->pembebasan * ($this->header->ndpbm->kurs_idr) );
        $nilai_dasar = $this->nilai_pabean - $nilai_pembebasan_idr;

        // assert that nilai dasar is valid
        if ($nilai_dasar < 0) {
            $nilai_pembebasan_idr = number_format($nilai_pembebasan_idr, 2);
            $nilai_pabean = number_format($this->nilai_pabean, 2);
            throw new \Exception("Cannot compute nilai pungutan: (DetailBarang #{$this->id}) Nilai Pembebasan (Rp {$nilai_pembebasan_idr}) > Nilai Pabean (Rp {$nilai_pabean})");
        }

        foreach ($tarif_bm as $kode => $tbm) {
            if ($tbm['jenis'] == 'SPESIFIK') {
                // hitung metode spesifik
                $bm = (float) $this->jumlah_satuan * $tbm['tarif'];
            } else {
                // hitung metode persentase
                $bm = ceil(/* $this->nilai_pabean */ $nilai_dasar * $tbm['tarif'] * 0.01 / 1000.0) * 1000.0;
            }
            // accumulate first
            $total_bm += $bm;

            // compute bayar, bebas, tunda, tanggung_pemerintah
            $bm_bayar = round($bm * ($tbm['bayar'] ?? 100.0) * 0.01, -3);
            $bm_bebas = round($bm * ($tbm['bebas'] ?? 0) * 0.01, -3);
            $bm_tunda = round($bm * ($tbm['tunda'] ?? 0) * 0.01, -3);
            $bm_tanggung_pemerintah = round($bm * ($tbm['tanggung_pemerintah'] ?? 0) * 0.01, -3);

            // spawn new pungutan
            $p = new Pungutan([
                'bayar' => $bm_bayar,
                'bebas' => $bm_bebas,
                'tunda' => $bm_tunda,
                'tanggung_pemerintah' => $bm_tanggung_pemerintah,
            ]);

            $p->jenisPungutan()->associate(ReferensiJenisPungutan::byKode($kode)->first());

            $pungutan[] = $p;
        }

        // #2 HITUNG PAJAK2nya
        $nilai_impor = $total_bm + $nilai_dasar;

        foreach ($tarif_pajak as $kode => $tp) {
            // hitung pajak
            $pajak = ceil($nilai_impor * $tp['tarif'] * 0.01 / 1000.0) * 1000.0;

            $bayar = round($pajak * ($tp['bayar'] ?? 100.0) * 0.01, -3);
            $bebas = round($pajak * ($tp['bebas'] ?? 0) * 0.01, -3);
            $tunda = round($pajak * ($tp['tunda'] ?? 0) * 0.01, -3);
            $tanggung_pemerintah = round($pajak * ($tp['tanggung_pemerintah'] ?? 0) * 0.01, -3);

            $p = new Pungutan([
                'bayar' => $bayar,
                'bebas' => $bebas,
                'tunda' => $tunda,
                'tanggung_pemerintah' => $tanggung_pemerintah,
            ]);
            $p->jenisPungutan()->associate(ReferensiJenisPungutan::byKode($kode)->first());

            $pungutan[] = $p;
        }

        return $pungutan;
    }

    public function getNiceFormatAttribute() {
        $desc = $this->uraian;
        $desc .= "\n" . number_format($this->brutto, 2) ." KG";
        // append all additional desc
        if ($this->detailSekunder()->count()) {
            $desc.= "\n------------------\n";
            foreach ($this->detailSekunder as $ds) {
                $desc .= $ds->referensiJenisDetailSekunder->nama . ' : ' . $ds->data . "\n";
            }
        }

        return $desc;
    }

    public function getPrintFormatAttribute() {
        $desc = $this->uraian;
        $desc .= "\n" . number_format($this->brutto, 2) ." KG" . ", {$this->jumlah_kemasan} {$this->jenis_kemasan}";
        if ($this->jumlah_satuan && $this->jenis_satuan) {
            $desc .= ", {$this->jumlah_satuan} {$this->jenis_satuan}";
        }
        if ($this->netto) {
            $desc .= ", Netto: " . number_format($this->netto, 2) . "KG";
        }
        // append all additional desc
        if ($this->detailSekunder()->count()) {
            $desc.= "\n------------------\n";
            foreach ($this->detailSekunder as $ds) {
                $desc .= $ds->referensiJenisDetailSekunder->nama . ' : ' . $ds->data . "\n";
            }
        }
        // append fasilitas?
        if ($this->fasilitas()->count()) {
            $desc.="\n*) fasilitas: [";
            $afterOne = false;
            foreach ($this->fasilitas as $f) {
                if ($afterOne) {
                    $desc .= ", ";
                }
                $desc .= $f->deskripsi;
                $afterOne = true;
            }
            $desc .= "]";
        }

        return $desc;
    }

    // HELPER
    // sync data with request ($d MUST BE EXISTING FOR SECONDARY DATA!!)

    /**
     * Sync all data that DOES NOT require DETAILBARANG TO EXIST!
     */
    public function syncPrimaryData(Request $r) {
        $this->uraian = expectSomething($r->get('uraian'), "Uraian Barang");
        $this->jumlah_kemasan = expectSomething($r->get('jumlah_kemasan'), "Jumlah Kemasan");
        $this->jenis_kemasan = expectSomething($r->get('jenis_kemasan'), "Jenis Kemasan");

        // data satuan optional
        $this->jumlah_satuan = $r->get('jumlah_satuan');
        $this->jenis_satuan = $r->get('jenis_satuan');

        // data nilai barang (isi default?)
        $this->fob = expectSomething($r->get('fob'), 'FOB');
        $this->insurance = expectSomething($r->get('insurance'), 'Insurance');
        $this->freight = expectSomething($r->get('freight'), 'Freight');

        $this->brutto = expectSomething($r->get('brutto'), 'Bruto');
        $this->netto = $r->get('netto');

        // associate the right kurs
        // $kurs = $r->get('kurs_id');
        $kurs_id = expectSomething($r->get('kurs_id'), "Kurs");
        $this->kurs()->associate(Kurs::findOrFail($kurs_id));

        // associate the right hs
        // $hs = $r->get('hs');
        $hs_id = expectSomething($r->get('hs_id'), "HS Code");
        $this->hs()->associate(HsCode::findOrFail($hs_id));
    }

    /**
     * Sync all data that requires DETAILBARANG TO EXIST FIRST!!
     */
    public function syncSecondaryData(Request $r) {
        if (!$this->exists()) {
            throw new \Exception("DetailBarang must be saved first or this operation will fail!");
        }

        // sync kategori tags here
        $kategori = Kategori::whereIn('nama', $r->get('kategori_tags', []))->get();
        $this->kategori()->sync($kategori);

        // sync detail sekunder here
        $this->syncDetailSekunder($r);

        // sync fasilitas here
        $this->syncFasilitas($r);
    }

    public function syncFasilitas(Request $r) {
        $fas = $r->get('fasilitas')['data'] ?? [];

        // 1st update all that has data
        $toUpdate = array_filter($fas, function ($e) { return $e['id']; });

        foreach ($toUpdate as $f) {
            $data = Fasilitas::findOrFail($f['id']);

            $data->jenis = $f['jenis'];
            $data->jenis_pungutan_id = $f['jenis_pungutan_id'];
            $data->tarif_keringanan = $f['tarif_keringanan'];
            $data->save();
        }

        // 2nd, delete anything not included
        $updateIds = array_map(function($e) { return $e['id']; }, $toUpdate);
        $this->fasilitas()->whereNotIn('id', $updateIds)->delete();

        // 3rd, save new fasilitas
        $toInsert = array_filter($fas, function($e) { return !$e['id']; });

        foreach ($toInsert as $f) {
            $data = new Fasilitas();

            $data->jenis = $f['jenis'];
            $data->jenis_pungutan_id = $f['jenis_pungutan_id'];
            $data->tarif_keringanan = $f['tarif_keringanan'];

            $this->fasilitas()->save($data);
        }
    }

    public function syncDetailSekunder(Request $r) {
        // sync detail sekunder data??
        $ds = $r->get('detailSekunder')['data'] ?? [];

        // 1st, update all that has data
        $toUpdate = array_filter($ds, function ($e) { return $e['id']; });

        foreach ($toUpdate as $s) {
            $data = DetailSekunder::findOrFail($s['id']);

            $data->data = $s['data'];
            $data->referensiJenisDetailSekunder()->associate(ReferensiJenisDetailSekunder::byName($s['jenis'])->first());
            $data->save();
        }

        // 2nd, delete the rest that is not included above
        $updateIds = array_map(function($e) { return $e['id']; }, $toUpdate);
        $this->detailSekunder()->whereNotIn('id', $updateIds)->delete();

        // 3rd, insert new detail sekunder
        $toInsert = array_filter($ds, function ($e) { return !$e['id']; });

        foreach ($toInsert as $s) {
            $data = new DetailSekunder();

            $data->data = $s['data'];
            $data->referensiJenisDetailSekunder()->associate(ReferensiJenisDetailSekunder::byName($s['jenis'])->first());

            $this->detailSekunder()->save($data);
        }
    }
}
