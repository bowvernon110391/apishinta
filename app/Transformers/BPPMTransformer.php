<?php
namespace App\Transformers;

use App\BPPM;
use League\Fractal\TransformerAbstract;

class BPPMTransformer extends TransformerAbstract {

    protected $availableIncludes = [
        'pejabat'
    ];

    protected $defaultIncludes = [
        'pejabat'
    ];

    public function transform(BPPM $b) {
        return [
            'id' => (int) $b->id,
            'nomor_lengkap' => $b->nomor_lengkap,
            'tgl_dok' => $b->tgl_dok
        ];
    }

    public function includePejabat(BPPM $b) {
        return $this->item($b->pejabat, new SSOUserCacheTransformer);
    }
}