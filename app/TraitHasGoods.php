<?php

namespace App;

trait TraitHasGoods {

    public function detailBarang() {
        return $this->morphMany(DetailBarang::class, 'header');
    }

    public function getJumlahDetailBarangAttribute() {
        return $this->detailBarang()->count();
    }

    public function onCreateItem(DetailBarang $d) {}
    public function onDeleteItem(DetailBarang $d) {}
    public function onUpdateItem(DetailBarang $d) {}
}