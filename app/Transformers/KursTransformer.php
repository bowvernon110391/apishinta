<?php
namespace App\Transformers;

use App\Kurs;
use League\Fractal\TransformerAbstract;

class KursTransformer extends TransformerAbstract {

    public function transform(Kurs $kurs) {
        return [
            'id'            => (int) $kurs->id,
            'kode_valas'    => $kurs->kode_valas,
            'jenis'         => $kurs->jenis,
            'kurs_idr'      => (float) $kurs->kurs_idr,
            'tanggal_awal'  => (string) $kurs->tanggal_awal,
            'tanggal_akhir' => (string) $kurs->tanggal_akhir,
            'created_at'    => (string) $kurs->created_at
        ];
    }
}

?>