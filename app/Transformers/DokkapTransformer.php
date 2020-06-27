<?php

namespace App\Transformers;

use App\Dokkap;
use League\Fractal\TransformerAbstract;

class DokkapTransformer extends TransformerAbstract {
    protected $availableIncludes = [
        'referensiJenisDokkap'
    ];

    public function transform(Dokkap $d) {
        return [
            'id' => (int) $d->id,
            'nomor_lengkap' => $d->nomor_lengkap_dok,
            'tgl_dok' => $d->tgl_dok,
            'jenis_dokkap_id' => (int) $d->jenis_dokkap_id
        ];
    }

    public function includeReferensiJenisDokkap(Dokkap $d) {
        $ref = $d->referensiJenisDokkap;

        return $this->item($ref, new ReferensiJenisDokkapTransformer);
    }
}