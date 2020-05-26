<?php
namespace App\Transformers;

use App\ST;
use League\Fractal\TransformerAbstract;

class STTransformer extends TransformerAbstract {
    // defaultly loaded relations
    protected $defaultIncludes = [
        'kurs',
        'negara_asal',
        'status'
    ];

    // available relations, default relations not needed to apply
    protected $availableIncludes = [
        'kurs',
        'cd',
        'negara_asal',
        'status'
    ];

    // basic transformation, without any sweetener
    public function transform(ST $s) {

        $result = [
            'id'                => $s->id,
            'no_dok'            => $s->no_dok,
            'tgl_dok'           => $s->tgl_dok,
            'nomor_lengkap'     => $s->nomor_lengkap,

            'jenis'             => $s->jenis,
            
            'lokasi'            => $s->lokasi ? $s->lokasi->nama : null,

            'total_fob'         => (float) $s->total_fob,
            'total_insurance'   => (float) $s->total_insurance,
            'total_freight'     => (float) $s->total_freight,
            'total_cif'         => (float) $s->total_cif,
            'total_nilai_pabean'=> (float) $s->total_nilai_pabean,
            
            'pembebasan'        => (float) $s->pembebasan,

            'total_bm'          => (float) $s->total_bm,
            'total_ppn'         => (float) $s->total_ppn,
            'total_ppnbm'       => (float) $s->total_ppnbm,
            'total_pph'         => (float) $s->total_pph,
            'total_denda'       => (float) $s->total_denda,
            
            'keterangan'        => $s->keterangan,

            'links'             => $s->links,

            'nama_pejabat'      => $s->nama_pejabat,
            'nip_pejabat'       => (string) $s->nip_pejabat,

            'pemilik_barang'    => $s->pemilik_barang,

            'last_status'       => $s->short_last_status,

            'is_locked'         => $s->is_locked,

            'package_summary'   => $s->cd ? $s->cd->package_summary : null,
            'package_summary_string'    => $s->cd ? $s->cd->package_summary_string : null,
            'total_brutto'      => $s->cd ? $s->cd->getTotalValue('brutto') : null,
            'uraian_summary'    => $s->cd ? $s->cd->uraian_summary : []

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