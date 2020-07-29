<?php
namespace App\Transformers;

use App\Status;
use League\Fractal\TransformerAbstract;

class StatusTransformer extends TransformerAbstract {

    protected $availableIncludes = [
        'detail',
        'user'
    ];

    protected $defaultIncludes = [
        'detail',
        'user'
    ];

    public function transform(Status $status) {
       
        return [
            'id'    => (int) $status->id,
            'status' => $status->status,
            'created_at' => (string) $status->created_at
        ];
    }

    public function includeDetail(Status $s) {
        if ($s->detail)
            return $this->item($s->detail, new StatusDetailTransformer);
    }

    public function includeUser(Status $s) {
        if ($s->user)
            return $this->item($s->user, new SSOUserCacheTransformer);
    }
}

