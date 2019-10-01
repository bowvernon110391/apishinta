<?php
namespace App\Transformers;

use App\Penumpang;
use League\Fractal\TransformerAbstract;

class PenumpangTransformer extends TransformerAbstract {
    // list possible embeds/includes here
    protected $availableIncludes = [];

    // list defaultly loaded embed/includes
    protected $defaultIncludes = [];

    // simple transform for data penumpang
    public function transform(Penumpang $p) {
        return [
            'id'    => $p->id,
            'nama'  => $p->nama,
            'tgl_lahir' => $p->tgl_lahir,
            'no_paspor' => $p->no_paspor,
            'kebangsaan'=> $p->kebangsaan,
            'pekerjaan' => $p->pekerjaan,
            'created_at'    => (string) $p->created_at,
            'updated_at'    => (string) $p->updated_at
        ];
    }
}

?>