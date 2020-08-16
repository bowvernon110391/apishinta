<?php

namespace App;

use Illuminate\Database\Eloquent\Collection;

trait TraitHasGoods {

    public function detailBarang() {
        return $this->morphMany(DetailBarang::class, 'header');
    }

    public function getJumlahDetailBarangAttribute() {
        return $this->detailBarang()->count();
    }

    public function summaryPackage(Collection $barang) {
        $summary = $barang->reduce(function ($acc, $e) {
            // append to $acc if it doesn't exist yet
            if (!isset($acc[$e->jenis_kemasan])) {
                $acc[$e->jenis_kemasan] = (float) $e->jumlah_kemasan;
            } else {
                $acc[$e->jenis_kemasan] += (float) $e->jumlah_kemasan;
            }

            return $acc;
        }, []);

        return $summary;
    }

    public function onCreateItem(DetailBarang $d) {}
    public function onDeleteItem(DetailBarang $d) {}
    public function onUpdateItem(DetailBarang $d) {}

    public function computePungutanImpor() {
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

    /**
     * copyDetailBarang
     *  copy detail barang dari sumber
     */
    public function copyDetailBarang(IHasGoods $s, bool $zeroPembebasan = true) {
        $r = app('request');
        // gotta grab data barang first
        $barangs = $s->detailBarang;

        foreach ($barangs as $b) {
            $duplicate = $b->replicate();
            
            // $this->detailBarang()->save($duplicate);
            $duplicate->header()->associate($this);
            // zero out pembebasan
            if ($zeroPembebasan)
                $duplicate->pembebasan = 0;
                
            $duplicate->save();

            // if barang is penetapan, create one too
            if ($b->is_penetapan) {
                $p = new Penetapan();
                $p->penetapan()->associate($duplicate);
                $p->pejabat()->associate(SSOUserCache::byId($r->userInfo['user_id']));
                $p->save();
            }

            // trigger handler
            
            $this->onCreateItem($duplicate);
        }
    }
}