<?php
namespace App\Transformers;

use App\ReferensiJenisPungutan;
use League\Fractal\TransformerAbstract;

class ReferensiJenisPungutanTransformer extends TransformerAbstract {
    public function transform(ReferensiJenisPungutan $r) {
        return [
            'id' => (int) $r->id,
            'kode' => $r->kode,
            'nama' => $r->nama,
            'kode_akun' => $r->kode_akun
        ];
    }
}