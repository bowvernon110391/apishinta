<?php
namespace App\Transformers;

use App\BPPM;
use League\Fractal\TransformerAbstract;

class BPPMTransformer extends TransformerAbstract {

    protected $availableIncludes = [
        'pejabat',
        'billing'
    ];

    protected $defaultIncludes = [
        'pejabat'
    ];

    public function transform(BPPM $b) {
        return [
            'id' => (int) $b->id,
            'nomor_lengkap' => $b->nomor_lengkap,
            'tgl_dok' => $b->tgl_dok,

            'payable_type' => $b->payable_type,
            'payable_id' => (int) $b->payable_id,
            'payable_uri' => $b->payable->uri ?? null,

            'jenis_dokumen_payable' => $b->payable->jenis_dokumen,
            'jenis_dokumen_lengkap_payable' => $b->payable->jenis_dokumen_lengkap,
            'nomor_lengkap_payable' => $b->payable->nomor_lengkap,
            'tgl_dok_payable' => $b->payable->tgl_dok,

            'payer' => $b->payable->payer ?? [],

            'created_at' => (string) $b->created_at,
            'updated_at' => (string) $b->updated_at
        ];
    }

    public function includePejabat(BPPM $b) {
        return $this->item($b->pejabat, new SSOUserCacheTransformer);
    }

    public function includeBilling(BPPM $b) {
        return $this->collection($b->payable->billing, new BillingTransformer);
    }
}