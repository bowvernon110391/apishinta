<?php
namespace App\Transformers;

use App\Status;
use League\Fractal\TransformerAbstract;

class StatusTransformer extends TransformerAbstract {

    public function transform(Status $status) {
       
        return [
            'status' => $status->status,
            'created_at' => (string) $status->created_at
        ];
    }
}

