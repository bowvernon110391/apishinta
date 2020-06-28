<?php

namespace App;

/**
 * ISpecifiable
 * -- untuk semua object yang bisa ditetapkan (DetailBarang, misalnya)
 * -- wajib mengimiplementasikan interface ini
 */
interface ISpecifiable {
    // Penetapan ada 2 (tarif dan nilai)
    // Tarif bisa macem2 (many rows),
    // nilai = 1 row

    public function penetapan();
}