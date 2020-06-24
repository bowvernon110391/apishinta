<?php
namespace App\Transformers;

Trait TraitInstructableTransformer {
    public function includeInstruksiPemeriksaan($data) {
        $ip = $data->instruksiPemeriksaan;

        if ($ip)
            return $this->item($ip, new InstruksiPemeriksaanTransformer);
    }
}