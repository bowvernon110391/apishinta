<?php
namespace App\Transformers;

use App\LHP;
use League\Fractal\TransformerAbstract;

class LHPTransformer extends TransformerAbstract {
    
    // what's available
    protected $availableIncludes = [
        'lampiran',
        'inspectable'
    ];

    protected $defaultIncludes = [
        'lampiran'
    ];

    // default transform
    public function transform(LHP $l) {
        // just return the most interesting data?

        return [
            'id' => (int) $l->id,
            'no_dok' => (int) $l->no_dok,
            'tgl_dok' => (string) $l->tgl_dok,
            'nomor_lengkap' => $l->nomor_lengkap,

            'isi' => $l->isi,

            'tanggal_mulai' => $l->tanggal_mulai,
            'waktu_mulai' => $l->waktu_mulai,
            'tanggal_selesai' => $l->tanggal_mulai,
            'waktu_selesai' => $l->waktu_mulai,

            'nama_pejabat' => $l->nama_pejabat,
            'nip_pejabat' => $l->nip_pejabat,

            'last_status' => $l->short_last_status,
            'is_locked' => $l->is_locked
        ];
    }

    public function includeLampiran(LHP $l) {
        return $this->collection($l->lampiran, new LampiranTransformer);
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
}