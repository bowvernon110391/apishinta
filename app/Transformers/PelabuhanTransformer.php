<?php
namespace App\Transformers;

use App\Pelabuhan;
use League\Fractal\TransformerAbstract;

class PelabuhanTransformer extends TransformerAbstract {
    // simple transform for data pelabuhan
    public function transform(Pelabuhan $n) {
        return [
            'id'     => $n->id,
            'kode'   => $n->kode,
            'nama'   => $n->nama
        ];
    }
}

?>