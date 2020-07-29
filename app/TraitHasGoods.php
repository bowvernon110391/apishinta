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
}