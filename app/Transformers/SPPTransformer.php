<?php
namespace App\Transformers;

use App\SPP;
use League\Fractal\TransformerAbstract;

class SPPTransformer extends TransformerAbstract {
    use TraitInstructableTransformer;
    // defaultly loaded relations
    protected $defaultIncludes = [
        'cd',
        'negara_asal',
        'status',

        'instruksi_pemeriksaan'
    ];

    // available relations, default relations not needed to apply
    protected $availableIncludes = [
        'cd',
        'negara_asal',
        'status',

        'instruksi_pemeriksaan'
    ];

    // basic transformation, without any sweetener
    public function transform(SPP $s) {

        $result = [
            'id'                => $s->id,
            'no_dok'            => $s->no_dok,
            'tgl_dok'           => $s->tgl_dok,
            'nomor_lengkap'     => $s->nomor_lengkap,
            
            'lokasi'            => $s->lokasi->kode ?? null,

            'links'             => $s->links,
            
            'last_status'       => $s->short_last_status,

            'is_locked'         => $s->is_locked,

            'keterangan'        => $s->keterangan[0]->keterangan ?? null
        ];

        return $result;
    }

    // include status
    public function includeStatus(SPP $s) {
        $status = collect($s->statusOrdered());
        return $this->collection($status, new StatusTransformer);
    }

    // include negara asal
    public function includeNegaraAsal(SPP $s) {
        $n = $s->negara;

        return $this->item($n, new NegaraTransformer);
    }

    // include cd
    public function includeCD(SPP $s) {
        $cd = $s->cd;

        return $this->item($cd, new CDTransformer);
    }
}

?>