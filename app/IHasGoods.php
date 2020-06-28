<?php

namespace App;

/**
 * Interface untuk setiap jenis dokumen yang punya data barang (CD, PIBK)
 */

interface IHasGoods {
    public function detailBarang(); // relation
    public function getJumlahDetailBarangAttribute();
    // public function getJumlahKoliAttribute();
}