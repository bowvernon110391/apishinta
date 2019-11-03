<?php
namespace App\Transformers;

use App\Kategori;
use League\Fractal\TransformerAbstract;

class KategoriTransformer extends TransformerAbstract {
    public function transform(Kategori $k) {
        return [
            'id'    => (int) $k->id,
            'nama'  => $k->nama
        ];
    }
}