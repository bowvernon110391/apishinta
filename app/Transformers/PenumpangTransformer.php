<?php
namespace App\Transformers;

use App\Penumpang;
use League\Fractal\TransformerAbstract;

class PenumpangTransformer extends TransformerAbstract {
    // simple transform for data penumpang
    public function transform(Penumpang $p) {
        return [
            'id'    => $p->id,
            'nama'  => $p->nama,
            'tgl_lahir' => $p->tgl_lahir
        ];
    }
}

?>