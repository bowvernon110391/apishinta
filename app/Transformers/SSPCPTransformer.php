<?php
namespace App\Transformers;

use App\SSPCP;
use League\Fractal\TransformerAbstract;

class SSPCPTransformer extends TransformerAbstract {
    // some available includes
    protected $availableIncludes = [
        'billable',
        'status',
        'lampiran'
    ];

    // default transform
    public function transform(SSPCP $s) {
        $ret = [
            'id' => (int) $s->id,
            'no_dok' => (int) $s->no_dok,
            'tgl_dok' => (string) $s->tgl_dok,
            'nomor_lengkap' => (string) $s->nomor_lengkap,
            'jenis' => $s->jenis,
            'total_bm' => (float) $s->total_bm,
            'total_ppn' => (float) $s->total_ppn,
            'total_pph' => (float) $s->total_pph,
            'total_ppnbm' => (float) $s->total_ppnbm,
            'total_denda' => (float) $s->total_denda,
            'kode_valuta' => $s->kode_valuta,
            'nilai_valuta' => $s->nilai_valuta,

            'nama_wajib_bayar' => $s->nama_wajib_bayar,
            'npwp_wajib_bayar' => $s->npwp_wajib_bayar,
            'no_identitas_wajib_bayar' => $s->no_identitas_wajib_bayar,
            'jenis_identitas_wajib_bayar' => $s->jenis_identitas_wajib_bayar,
            'alamat_wajib_bayar' => $s->alamat_wajib_bayar,

            'nama_pejabat' => $s->nama_pejabat,
            'nip_pejabat' => $s->nip_pejabat,

            'created_at' => (string) $s->created_at,
            'updated_at' => (string) $s->updated_at,

            'last_status' => $s->short_last_status,
            'is_locked' => $s->is_locked,
            'links' => $s->links
        ];

        return $ret;
    }

    // include status
    public function includeStatus(SSPCP $s) {
        return $this->collection($s->statusOrdered(), new StatusTransformer);
    }

    // include lampiran
    public function includeLampiran(SSPCP $s) {
        return $this->collection($s->lampiran, new LampiranTransformer);
    }
}