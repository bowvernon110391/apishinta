<?php
namespace App\Transformers;

use App\SPMB;
use League\Fractal\TransformerAbstract;

class SPMBTransformer extends TransformerAbstract {
    // default loaded relations
    protected $defaultIncludes = [
        'penumpang',
        'status',
        'negara_tujuan'
    ];

    // available includes
    protected $availableIncludes = [
        'penumpang',
        'status',
        'negara_tujuan',
        'details',
        'airline',
        'cd'
    ];

    // basic transformation
    public function transform(SPMB $s) {
        $result = [
            'id'        => (int) $s->id,
            'no_dok'    => (int) $s->no_dok,
            'tgl_dok'   => (string) $s->tgl_dok,
            'nomor_lengkap' => $s->nomor_lengkap,
            'lokasi'    => $s->lokasi->nama,

            // flight data
            'no_flight_berangkat'   => (string) $s->no_flight_berangkat,
            'tgl_berangkat'     => (string) $s->tgl_berangkat,

            // isi dok
            'maksud_pembawaan'  => (string) $s->maksud_pembawaan,

            // timestamps
            'created_at'    => (string) $s->created_at,
            'updated_at'    => (string) $s->updated_at,

            'last_status'   => $s->short_last_status,
            'links'         => $s->links
        ];

        return $result;
    }

    // include penumpang
    public function includePenumpang(SPMB $s) {
        $penumpang = $s->penumpang;

        return $this->item($penumpang, new PenumpangTransformer);
    }

    // include status
    public function includeStatus(SPMB $s) {
        $status = collect($s->statusOrdered());
        return $this->collection($status, new StatusTransformer);
    }

    // include negara tujuan
    public function includeNegaraTujuan(SPMB $s) {
        $negaraTujuan = $s->negaraTujuan;
        return $this->item($negaraTujuan, new NegaraTransformer);
    }

    // include airline
    public function includeAirline(SPMB $s) {
        $airline = $s->airline;
        return $this->item($airline, new AirlineTransformer);
    }

    // include cd
    public function includeCD(SPMB $s) {
        $cd = $s->cd;

        return $this->item($cd, new CDTransformer);
    }
}