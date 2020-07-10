<?php
namespace App\Transformers;

use App\SPPB;
use League\Fractal\TransformerAbstract;

class SPPBTransformer extends TransformerAbstract {
    public function transform(SPPB $s) {
        return [
            'id' => (int) $s->id,
            'nomor_lengkap' => $s->nomor_lengkap_dok,
            'tgl_dok' => $s->tgl_dok
        ];
    }
}