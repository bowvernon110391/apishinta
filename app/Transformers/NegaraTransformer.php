<?php
namespace App\Transformers;

use App\Negara;
use League\Fractal\TransformerAbstract;

class NegaraTransformer extends TransformerAbstract {
    // list possible embeds/includes here
    protected $availableIncludes = [];

    // list defaultly loaded embed/includes
    protected $defaultIncludes = [];

    // simple transform for data penumpang
    public function transform(Negara $n) {
        return [
            'id'     => $n->id,
            'kode'   => $n->kode,
            'uraian' => $n->uraian
        ];
    }
}

?>