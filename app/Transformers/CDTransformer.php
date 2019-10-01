<?php
namespace App\Transformers;

use App\CD;
use League\Fractal\TransformerAbstract;

class CDTransformer extends TransformerAbstract {
    // defaultly loaded relations
    protected $defaultIncludes = [
        'penumpang'
    ];

    // available relations, default relations not needed to apply
    protected $availableIncludes = [
        'details',
        'status'
    ];

    // basic transformation, without any sweetener
    public function transform(CD $cd) {
        $result = [
            'id'        => (int) $cd->id,
            'no_dok'    => (int) $cd->no_dok,
            'tgl_dok'   => (string) $cd->tgl_dok,
            'nomor_lengkap' => $cd->nomor_lengkap,
            'lokasi'    => $cd->lokasi->nama,
            'declare_flags' => $cd->flat_declare_flags,

            'jumlah_detail' => $cd->details()->count(),

            'created_at'    => (string) $cd->created_at,
            'updated_at'    => (string) $cd->updated_at,

            'last_status'   => $cd->short_last_status,
            
            'is_locked'   => $cd->is_locked,

            'links' => $cd->links
                
        ];

        return $result;
    }

    // include penumpang?
    public function includePenumpang(CD $cd) {
        $penumpang = $cd->penumpang;
        // cmn ada satu penumpang, perlakukan sbg item tunggal
        return $this->item($penumpang, new PenumpangTransformer);
    }

    // include details
    public function includeDetails(CD $cd) {
        return $this->collection($cd->details, new DetailCDTransformer);
    }

    // include last status
    // public function includeLastStatus(CD $cd) {
    //     return $this->item($cd->last_status, new StatusTransformer);
    // }

     // include last status
    public function includeStatus(CD $cd) {
        $status = collect($cd->status()->latest()->get());
        return $this->collection($status, new StatusTransformer);
    }
}

?>