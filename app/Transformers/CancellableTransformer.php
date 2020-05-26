<?php

use App\Cancellable;
use League\Fractal\TransformerAbstract;


class CancellableTransformer extends TransformerAbstract {
    public function transform(Cancellable $c) {
        return [
            'id'    => (int) $c->id,
            'cancellable_type'  => $c->cancellable_type,
            'cancellable_id'    => $c->cancellable_id,

            'created_at'        => (string) $c->created_at,
            'updated_at'        => (string) $c->updated_at
        ];
    }
}