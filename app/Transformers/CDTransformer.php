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
        'penumpang'
    ];

    // basic transformation, without any sweetener
    public function transform(CD $cd) {
        return [
            'id'        => (int) $cd->id,
            'no_dok'    => (int) $cd->no_dok,
            'tgl_dok'   => (string) $cd->tgl_dok,
            'nomor_lengkap' => $cd->nomor_lengkap,
            'lokasi'    => $cd->lokasi->nama,
            'declare_flags' => $cd->flat_declare_flags,

            'created_at'    => (string) $cd->created_at,
            'updated_at'    => (string) $cd->updated_at,

            'links' => [
                [
                    'rel'   => 'self',
                    'uri'   => '/dokumens/cd/' . $cd->id
                ],
                [
                    'rel'   => 'penumpang',
                    'uri'   => '/penumpangs/' . $cd->penumpang->id
                ],
                
            ]
        ];
    }

    // include penumpang?
    public function includePenumpang(CD $cd) {
        $penumpang = $cd->penumpang;
        // cmn ada satu penumpang, perlakukan sbg item tunggal
        return $this->item($penumpang, new PenumpangTransformer);
    }
}

?>