<?php
namespace App\Transformers;

use App\PJT;
use League\Fractal\TransformerAbstract;

class PJTTransformer extends TransformerAbstract {
    public function transform(PJT $p) {
        return [
            'id' => (int) $p->id,
            'npwp' => $p->npwp,
            'nama' => $p->nama,
            'alamat' => $p->alamat
        ];
    }
}