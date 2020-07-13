<?php
namespace App\Transformers;

use App\Billing;
use League\Fractal\TransformerAbstract;

class BillingTransformer extends TransformerAbstract {
    public function transform(Billing $b) {
        return [
            'id' => (int) $b->id,
            'nomor' => $b->nomor,
            'tanggal' => $b->tanggal,

            'ntb' => $b->ntb,
            'ntpn' => $b->ntpn,
            'tgl_ntpn' => $b->tgl_ntpn
        ];
    }
}