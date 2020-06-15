<?php
namespace App\Transformers;

use App\Status;
use League\Fractal\TransformerAbstract;

class StatusTransformer extends TransformerAbstract {

    protected $availableIncludes = [
        'detail'
    ];

    protected $defaultIncludes = [
        'detail'
    ];

    public function transform(Status $status) {
       
        return [
            'status' => $status->status,
            'created_at' => (string) $status->created_at
        ];
    }

    public function includeDetail(Status $s) {
        return $this->item($s->detail, new StatusDetailTransformer);
    }
}

