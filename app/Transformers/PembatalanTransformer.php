<?php
namespace App\Transformers;

use App\Pembatalan;
use App\Transformers\StatusTransformer;
use League\Fractal\TransformerAbstract;

class PembatalanTransformer extends TransformerAbstract {
    // includes
    protected $availableIncludes = [
        'cancellable',
        'status',
        'lampiran'
    ];

    protected $defaultIncludes = [
        'cancellable'
    ];

    // transform data for output
    public function transform(Pembatalan $p) {
        return [
            'id'        => (int) $p->id,
            'no_dok'    => (int) $p->no_dok,
            'nomor_lengkap' => $p->nomor_lengkap_dok,
            'tgl_dok'   => (string) $p->tgl_dok,
            'nama_pejabat'  => (string) $p->nama_pejabat,
            'nip_pejabat'   => (string) $p->nip_pejabat,
            'keterangan'    => (string) $p->keterangan,

            'last_status'   => $p->short_last_status,
            'is_locked'     => $p->is_locked,

            'created_at'    => (string) $p->created_at,
            'updated_at'    => (string) $p->updated_at
        ];
    }

    // include status
    public function includeStatus(Pembatalan $p) {
        $status = collect($p->statusOrdered());
        return $this->collection($status, new StatusTransformer);
    }

    // include cancellable?
    public function includeCancellable(Pembatalan $p) {
        $cancellable = collect($p->cancellable);
        return $this->collection($cancellable, new CancellableTransformer);
    }

    // include lampiran?
    public function includeLampiran(Pembatalan $p) {
        $lampiran = collect($p->lampiran);
        return $this->collection($lampiran, new LampiranTransformer);
    }
}