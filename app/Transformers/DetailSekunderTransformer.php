<?php
namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\DetailSekunder;

class DetailSekunderTransformer extends TransformerAbstract {
    // simple transform
    public function transform(DetailSekunder $ds) {
        return [
            'id'    => (int) $ds->id,
            'jenis' => $ds->referensiJenisDetailSekunder->nama,
            'data'  => $ds->data
        ];
    }
}

?>