<?php
namespace App\Transformers;

use App\Lampiran;
use League\Fractal\TransformerAbstract;

class LampiranTransformer extends TransformerAbstract {
    protected $availableIncludes = [
        'attachable'
    ];

    public function transform(Lampiran $l) {
        return [
            'id'            => (int) $l->id,
            'jenis'         => $l->jenis,
            'mime_type'     => $l->mime_type,
            'diskfilename'  => $l->diskfilename,
            'filename'      => $l->filename,
            'filesize'      => $l->filesize,
            'url'           => $l->url,
            'owner_type'    => $l->owner_type,
            'created_at'    => (string) $l->created_at,
            'updated_at'    => (string) $l->updated_at
        ];
    }

    public function includeAttachable(Lampiran $l) {
        $data = $l->attachable;
        switch ($l->owner_type) {
            case 'cd':
            case 'CD':
                return $this->item($data, new CDTransformer);
                break;
            
            default:
                return null;
                break;
        }
    }
}
