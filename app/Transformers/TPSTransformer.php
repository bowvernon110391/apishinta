<?php
namespace App\Transformers;

use App\TPS;
use League\Fractal\TransformerAbstract;

class TPSTransformer extends TransformerAbstract {

    public function transform(TPS $t) {
        $data = [
            'id' => (int) $t->id,
            'kode' => $t->kode,
            'nama' => $t->nama,
            'alamat' => $t->alamat,
            'kode_kantor' => $t->kode_kantor
        ];

        return $data;
    }
}