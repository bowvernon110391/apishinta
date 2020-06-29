<?php
namespace App\Transformers;

use App\Tarif;
use League\Fractal\TransformerAbstract;

class TarifTransformer extends TransformerAbstract {


    public function transform(Tarif $t) {
        // basic transformation
        return [
            'id' => (int) $t->id,
            'jenis_pungutan_id' => (int) $t->jenis_pungutan_id,
            'kd_jenis_pungutan' => $t->jenisPungutan->kode,
            'jenis' => $t->jenis,

            'tarif' => (float) $t->tarif,
            'bayar' => (float) $t->bayar,
            'bebas' => (float) $t->bebas,
            'tunda' => (float) $t->tunda,
            'tanggung_pemerintah' => (float) $t->tanggung_pemerintah,

            'overridable' => (bool) $t->overridable
        ];
    }
}