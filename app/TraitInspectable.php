<?php

namespace App;

/**
 * Utk dokumen yang bisa diperiksa TANPA HARUS ADA INSTRUKSI PEMERIKSAAN
 */
trait TraitInspectable
{
    public function lhp() {
        return $this->morphMany(LHP::class, 'inspectable');
    }
}
