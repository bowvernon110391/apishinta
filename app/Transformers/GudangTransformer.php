<?php
namespace App\Transformers;

use App\Gudang;
use League\Fractal\TransformerAbstract;

class GudangTransformer extends TransformerAbstract {

    protected $availableIncludes = [
        'tps'
    ];

    protected $defaultIncludes = [
        'tps'
    ];

    public function transform(Gudang $g) {
        return [
            'id' => (int) $g->id,
            'kode' => $g->kode
        ];
    }

    public function includeTps(Gudang $g) {
        $t = $g->tps;

        if ($t) {
            return $this->item($t, new TPSTransformer);
        }
    }
}