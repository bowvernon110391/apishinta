<?php
namespace App\Transformers;

use App\ReferensiJenisDetailSekunder;
use League\Fractal\TransformerAbstract;

class ReferensiJenisDetailSekunderTransformer extends TransformerAbstract {
    // list possible embeds/includes here
    protected $availableIncludes = [];

    // list defaultly loaded embed/includes
    protected $defaultIncludes = [];

    // simple transform for data penumpang
    public function transform(ReferensiJenisDetailSekunder $k) {
        return [
            'id'     => $k->id,
            'nama'   => $k->nama
        ];
    }
}

?>