<?php
namespace App\Transformers;

use App\Airline;
use League\Fractal\TransformerAbstract;

class AirlineTransformer extends TransformerAbstract {
    // possible embeds
    protected $availableIncludes = [];

    // default embeds
    protected $defaultIncludes = [];

    // simple transform
    public function transform(Airline $a) {
        return [
            'id'    => $a->id,
            'kode'  => $a->kode,
            'uraian'=> $a->uraian
        ];
    }
}

?>