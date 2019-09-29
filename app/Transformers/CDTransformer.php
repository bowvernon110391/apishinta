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
        'details'
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

            'links' => [
                [
                    'rel'   => 'self',
                    'uri'   => '/dokumen/cd/' . $cd->id
                ],
                [
                    'rel'   => 'cd.penumpang',
                    'uri'   => '/penumpang/' . $cd->penumpang->id
                ],
                
            ]
        ];

        // append additional links if available
        if ($cd->sspcp) {
            $result['links'][] = [
                'rel'   => 'cd.sspcp',
                'uri'   => '/dokumen/sspcp/' . $cd->sspcp->id
            ];
        }

        if ($cd->imporSementara) {
            $result['links'][] = [
                'rel'   => 'cd.impor_sementara',
                'uri'   => '/dokumen/is/' . $cd->imporSementara->id
            ];
        }

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
}

?>