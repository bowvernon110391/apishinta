<?php
namespace App\Transformers;

use App\Billing;
use League\Fractal\TransformerAbstract;

class BillingTransformer extends TransformerAbstract {
    public function transform(Billing $b) {
        return [
            'id' => (int) $b->id,
            'nomor' => $b->nomor,
            'tanggal' => $b->tanggal
        ];
    }
}