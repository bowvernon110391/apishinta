<?php

namespace App;

trait TraitHasGoodsAndSpecifiable {

    public function detail() {
        return $this->morphMany(DetailBarang::class, 'header');
    }

    public function detailBarang() {
        return $this->detail()->whereDoesntHave('penetapanHeader');
    }

    public function penetapan() {
        return $this->detail()->whereHas('penetapanHeader');
    }

    public function getJumlahDetailBarangAttribute() {
        return $this->detailBarang()->count();
    }
}