<?php
namespace App\Transformers;

use App\Cancellable;
use League\Fractal\TransformerAbstract;


class CancellableTransformer extends TransformerAbstract {
    public function transform(Cancellable $c) {
        $data  = $c->instance;
        $detail = [];

        if ($data) {
            $detail['nomor_lengkap']    = $data->nomor_lengkap_dok;
            $detail['tgl_dok']          = $data->tgl_dok;
        }

        return [
            'id'    => (int) $c->id,
            'cancellable_type'  => $c->cancellable_type,
            'cancellable_id'    => $c->cancellable_id,

            'detail'            => $detail,

            'created_at'        => (string) $c->created_at,
            'updated_at'        => (string) $c->updated_at
        ];
    }
}