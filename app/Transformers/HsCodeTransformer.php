<?php
namespace App\Transformers;

use App\HsCode;
use League\Fractal\TransformerAbstract;

class HsCodeTransformer extends TransformerAbstract {
    public function transform(HsCode $hs) {
        return [
            'id'    => (int) $hs->id,
            'usable' => (boolean) $hs->usable,
            'takik' => (int) $hs->takik,
            'kode'  => $hs->kode,
            'uraian'=> $hs->uraian,
            'jenis_tarif' => $hs->jenis_tarif,
            'bm_tarif' => (float) $hs->bm_tarif,
            'ppn_tarif'=> (float) $hs->ppn_tarif,
            'ppnbm_tarif' => (float) $hs->ppnbm_tarif,
            'ppn_text' => $hs->ppn_text,
            'ppnbm_text' => $hs->ppnbm_text,
            'satuan_spesifik' => $hs->satuan_spesifik
        ];
    }
}