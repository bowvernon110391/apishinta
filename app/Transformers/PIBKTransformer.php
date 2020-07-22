<?php
namespace App\Transformers;

use App\PIBK;
use League\Fractal\TransformerAbstract;

class PIBKTransformer extends TransformerAbstract {

    public function transform(PIBK $p) {
        // use common theme
        return [
            'id' => (int) $p->id,
            'no_dok' => (int) $p->no_dok,
            'tgl_dok' => $p->tgl_dok,
            'nomor_lengkap' => $p->nomor_lengkap_dok,
            
            // data barang
            'koli' => (int) $p->koli,

            // bc11
            'no_bc11' => $p->no_bc11,
            'tgl_bc11' => $p->tgl_bc11,
            'pos_bc11' => $p->pos_bc11,
            'subpos_bc11' => $p->subpos_bc11,
            'subsubpos_bc11' => $p->subsubpos_bc11,

            // tarif
            'pph_tarif' => (float) $p->pph_tarif,

            // pelabuhan n flight
            'kd_pelabuhan_asal' => (string) $p->kd_pelabuhan_asal,
            'kd_pelabuhan_tujuan' => (string) $p->kd_pelabuhan_tujuan,

            'kd_airline' => (string) $p->kd_airline,
            'no_flight' => (string) $p->no_flight,

            // importir
            'alamat' => (string) $p->alamat,
            'npwm' => (string) $p->npwp
        ];
    }
}