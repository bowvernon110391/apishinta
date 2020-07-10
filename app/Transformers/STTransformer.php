<?php
namespace App\Transformers;

use App\ST;
use League\Fractal\TransformerAbstract;

class STTransformer extends TransformerAbstract {
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
    public function transform(ST $s) {

        $result = [
            'id'                => $s->id,
            'no_dok'            => $s->no_dok,
            'tgl_dok'           => $s->tgl_dok,
            'nomor_lengkap'     => $s->nomor_lengkap,

            'jenis'             => $s->jenis,
            
            'lokasi'            => $s->lokasi->kode ?? null,

            'links'             => $s->links,

            'last_status'       => $s->short_last_status,

            'is_locked'         => $s->is_locked,

            'keterangan'        => $s->keterangan[0]->keterangan ?? null
        ];

        return $result;
    }

    // include status
    public function includeStatus(ST $s) {
        $status = collect($s->statusOrdered());
        return $this->collection($status, new StatusTransformer);
    }

    // include negara asal
    public function includeNegaraAsal(ST $s) {
        $n = $s->negara;

        return $this->item($n, new NegaraTransformer);
    }

    // include kurs
    public function includeKurs(ST $s) {
        $k = $s->kurs;

        return $this->item($k, new KursTransformer);
    }

    // include cd
    public function includeCD(ST $s) {
        $cd = $s->cd;

        return $this->item($cd, new CDTransformer);
    }
}

?>