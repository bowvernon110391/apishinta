<?php

namespace App\Transformers;

use App\DetailBarang;
use League\Fractal\TransformerAbstract;

class DetailBarangTransformer extends TransformerAbstract {
    // some available includes
    protected $availableIncludes = [
        'header',
        'kurs',
        'hs',
        'detailSekunder',

        'kemasan',
        'satuan',
        'tags',

        'tarif',

        'penetapan', // kalau ini detil pengajuan, dan sudah ditetapkan
        'pengajuan', // kalau ini detil penetapan, dan sblmnya ada pengajuan
        'pejabat', // pejabat penetapan (kalau penetapan)

        'fasilitas' // fasilitas (kalau ada)
    ];

    protected $defaultIncludes = [
        'kurs',
        'hs',
        'detailSekunder',
        'kemasan',
        'satuan',
        'tarif',
        'fasilitas'
    ];

    // basic transform
    public function transform(DetailBarang $d) {
        return [
            'id' => (int) $d->id,
            'uraian' => $d->uraian,
            'jumlah_kemasan' =>  (float) $d->jumlah_kemasan,
            'jenis_kemasan' => $d->jenis_kemasan,
            'jumlah_satuan' => is_numeric($d->jumlah_satuan) ? (float) $d->jumlah_satuan : null,
            'jenis_satuan' => $d->jenis_satuan,

            'fob' => (float) $d->fob,
            'insurance' => (float) $d->insurance,
            'freight' => (float) $d->freight,

            'nilai_pabean' => (float) $d->nilai_pabean,

            'hs_id' => (int) $d->hs_id,
            'kurs_id' => (int) $d->kurs_id,

            'brutto' => (float) $d->brutto,
            'netto' => is_numeric($d->netto) ? (float) $d->netto : null,

            'kategori_tags' => $d->kategori_tags,

            'pembebasan' => (float) $d->pembebasan,

            // is it penetapan?
            'is_penetapan' => $d->is_penetapan
        ];
    }

    public function includeKurs(DetailBarang $d) {
        $k = $d->kurs;
        if ($k) {
            return $this->item($k, new KursTransformer);
        }
    }

    public function includeHs(DetailBarang $d) {
        $h = $d->hs;
        if ($h) {
            return $this->item($h, new HsCodeTransformer);
        }
    }

    public function includeDetailSekunder(DetailBarang $d) {
        $ds = $d->detailSekunder;
        return $this->collection($ds, new DetailSekunderTransformer);
    }

    public function includeKemasan(DetailBarang $d) {
        $k = $d->jenisKemasan;
        return $this->item($k, new KemasanTransformer);
    }

    public function includeSatuan(DetailBarang $d) {
        $s = $d->jenisSatuan;
        if ($s) {
            return $this->item($s, new SatuanTransformer);
        }
    }

    public function includeTags(DetailBarang $d) {
        $k = $d->kategori;
        return $this->collection($k, new KategoriTransformer);
    }

    public function includeTarif(DetailBarang $d) {
        $t = $d->tarif;
        return $this->collection($t, new TarifTransformer);
    }

    // self recurse lol
    public function includePenetapan(DetailBarang $d) {
        $p = $d->penetapan;
        if ($p) {
            return $this->item($p, new DetailBarangTransformer);
        }
    }

    public function includePengajuan(DetailBarang $d) {
        $p = $d->pengajuan;
        if ($p) {
            return $this->item($p, new DetailBarangTransformer);
        }
    }

    public function includePejabat(DetailBarang $d) {
        if ($d->is_penetapan) {
            $p = $d->pivotPenetapan->pejabat;
            if ($p) {
                return $this->item($p, new SSOUserCacheTransformer);
            }
        }
    }

    // header is variant
    public function includeHeader(DetailBarang $d) {
        if ($d->header) {
            $classname = class_basename($d->header);
            $transformerName = 'App\\Transformers\\' . $classname . 'Transformer';

            if (class_exists($transformerName)) {
                return $this->item($d->header, new $transformerName);
            }
        }
    }

    // include all fasilitas
    public function includeFasilitas(DetailBarang $d) {
        return $this->collection($d->fasilitas, new FasilitasTransformer);
    }
}
