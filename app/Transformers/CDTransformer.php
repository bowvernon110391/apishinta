<?php
namespace App\Transformers;

use App\CD;
use League\Fractal\TransformerAbstract;

class CDTransformer extends TransformerAbstract {
    use TraitInstructableTransformer;
    // defaultly loaded relations
    protected $defaultIncludes = [
        'penumpang',
        'ndpbm',
        'status',
        'lampiran',

        'instruksiPemeriksaan'
    ];

    // available relations, default relations not needed to apply
    protected $availableIncludes = [
        'penumpang',
        'details',
        'status',
        'pelabuhan_asal',
        'pelabuhan_tujuan',
        'ndpbm',
        'lampiran',
        'airline',

        'instruksiPemeriksaan'
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

            // keluarga n pembebasan
            'jml_anggota_keluarga'  => (int) $cd->jml_anggota_keluarga,
            'jml_bagasi_dibawa'     => (int) $cd->jml_bagasi_dibawa,
            'jml_bagasi_tdk_dibawa' => (int) $cd->jml_bagasi_tdk_dbawa,

            'pembebasan'    => (float) $cd->pembebasan,

            'pph_tarif' => (float) $cd->pph_tarif,

            'alamat'    => $cd->alamat,

            'penumpang_id'  => (int) $cd->penumpang_id,

            'npwp_nib'      => (string) ($cd->nib ? $cd->nib : $cd->npwp),
            'no_flight'     => (string) $cd->no_flight,
            'kd_airline'    => (string) $cd->kd_airline,
            'tgl_kedatangan'    => (string) $cd->tgl_kedatangan,

            'kd_pelabuhan_asal' => (string) $cd->kd_pelabuhan_asal,
            'kd_pelabuhan_tujuan' => (string) $cd->kd_pelabuhan_tujuan,

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
        // $status = collect($cd->status()->latest()->get());
        $status = collect($cd->statusOrdered());
        return $this->collection($status, new StatusTransformer);
    }

    // include pelabuhan
    public function includePelabuhanAsal(CD $cd) {
        $pa = $cd->pelabuhanAsal;
        return $this->item($pa, new PelabuhanTransformer);
    }

    public function includePelabuhanTujuan(CD $cd) {
        $pt = $cd->pelabuhanTujuan;
        return $this->item($pt, new PelabuhanTransformer);
    }

    public function includeAirline(CD $cd) {
        $airline = $cd->airline;
        return $this->item($airline, new AirlineTransformer);
    }

    public function includeNdpbm(CD $cd) {
        $ndpbm = $cd->ndpbm;
        return $this->item($ndpbm, new KursTransformer);
    }

    public function includeLampiran(CD $cd) {
        return $this->collection($cd->lampiran, new LampiranTransformer);
    }
}

?>