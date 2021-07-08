<?php
namespace App\Transformers;

use App\Fasilitas;
use League\Fractal\TransformerAbstract;

class FasilitasTransformer extends TransformerAbstract {
    protected $availableIncludes = [
        'detail_barang',
        'jenis_pungutan'
    ];

    public function transform(Fasilitas $f) {
        return [
            'id' => (int) $f->id,
            'jenis' => $f->jenis,
            'jenis_pungutan_id' => $f->jenis_pungutan_id,
            'tarif_keringanan' => $f->tarif_keringanan,
            'deskripsi' => $f->deskripsi
        ];
    }
}
