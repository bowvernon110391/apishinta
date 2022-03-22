<?php

namespace App\Transformers;

use App\CD;
use App\DetailBarang;
use App\PIBK;
use League\Fractal\TransformerAbstract;

class PenetapanTransformer extends TransformerAbstract {

    protected $availableIncludes = [
        'pungutan',
        'tarif',
        'header_info',
        'kategori'
    ];

    protected $defaultIncludes = [
        'tarif'
    ];

    public function transform(DetailBarang $e) {
        // hopefully the user is smart enough
        // to eager load 'header.ndpbm'
        // $usd = $e->header->ndpbm;

        return [

            // 'pungutan' => $e->computePungutanImporWithPembebasan(),
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
            'ndpbm' => (float) $e->kurs->kurs_idr,

            // pembebasan
            /* 'pembebasan' => (float) $e->pembebasan,
            'pembebasan_idr' => (float) $e->pembebasan * (float) $usd->kurs_idr,
            'nilai_dasar' => (float) $e->nilai_pabean - ((float) $e->pembebasan * (float) $usd->kurs_idr), */

            // timetstamps
            'created_at' => (string) $e->created_at,
            'updated_at' => (string) $e->updated_at,
        ];
    }

    public function includeTarif(DetailBarang $e) {
        return $this->primitive(['data' => $e->valid_tarif]);
    }

    public function includeKategori(DetailBarang $e) {
        return $this->collection($e->kategori, new KategoriTransformer);
    }

    public function includeHeaderInfo(DetailBarang $e) {
        return $this->primitive(['data' => [
            'header_type' => $e->header_type,
            'header_id' => $e->header_id,
            'nomor_lengkap' => $e->header->nomor_lengkap,
            'tgl_dok' => (string) $e->header->tgl_dok
        ]]);
    }

    public function includePungutan(DetailBarang $e) {
        $data = [];
        if ($e->header instanceof CD) {
            // gotta check if it's commercial
            if ($e->header->komersil) {
                $data = $e->computePungutanImpor();
            } else {
                $data = $e->computePungutanImporWithPembebasan();
            }
        } else if ($e->header instanceof PIBK) {
            $data = $e->computePungutanImpor();
        } else {
            // error
            throw new \Exception("Unknown header type for detail barang: " . get_class($e->header));
        }


        return $this->primitive(['data' => $data]);
    }
}
