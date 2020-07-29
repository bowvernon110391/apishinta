<?php
namespace App\Transformers;

use App\LHP;
use League\Fractal\TransformerAbstract;

class LHPTransformer extends TransformerAbstract {
    
    // what's available
    protected $availableIncludes = [
        'lampiran',
        'inspectable',
        'status'
    ];

    protected $defaultIncludes = [
        'lampiran'
    ];

    // default transform
    public function transform(LHP $l) {
        // just return the most interesting data?
        $instructable_uri = $l->instructable ? $l->instructable->uri : ($l->inspectable ? $l->inspectable->uri : null);

        $pemeriksa = $l->pemeriksa ? [
            'nama' => $l->pemeriksa->name,
            'nip' => $l->pemeriksa->nip
        ] : null;

        return [
            'id' => (int) $l->id,
            'no_dok' => (int) $l->no_dok,
            'tgl_dok' => (string) $l->tgl_dok,
            'nomor_lengkap' => $l->nomor_lengkap,

            'isi' => $l->isi,

            'pemeriksa_id' => (int) $l->pemeriksa_id,

            'pemeriksa' => $pemeriksa,

            'lokasi' => $l->lokasi ? $l->lokasi->kode : null,

            'instructable_uri' => $instructable_uri,

            'last_status' => $l->short_last_status,
            'is_locked' => $l->is_locked,

            'created_at' => (string) $l->created_at,
            'updated_at' => (string) $l->updated_at
        ];
    }

    public function includeLampiran(LHP $l) {
        return $this->collection($l->lampiran, new LampiranTransformer);
    }

    public function includeStatus(LHP $l) {
        $ret = $l->statusOrdered();
        if (count($ret)) {
            return $this->collection($ret, new StatusTransformer);
        }
    }

    public function includeInspectable(LHP $l) {
        if ($l->inspectable) {
            $classname = class_basename($l->inspectable);
            $transformerName = 'App\\Transformers\\' . $classname . 'Transformer';

            if (class_exists($transformerName)) {
                return $this->item($l->inspectable, new $transformerName);
            }
        }
    }

    public function includeInstructable(LHP $l) {
        if ($l->instructable) {
            $classname = class_basename($l->instructable);
            $transformerName = 'App\\Transformers\\' . $classname . 'Transformer';

            if (class_exists($transformerName)) {
                return $this->item($l->instructable, new $transformerName);
            }
        }
    }
}