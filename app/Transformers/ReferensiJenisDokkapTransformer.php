<?php

namespace App\Transformers;

use App\ReferensiJenisDokkap;
use League\Fractal\TransformerAbstract;

class ReferensiJenisDokkapTransformer extends TransformerAbstract {
    public function transform(ReferensiJenisDokkap $r) {
        return [
            'id' => (int) $r->id,
            'nama' => $r->nama,
            'usable' => (bool) $r->usable
        ];
    }
}