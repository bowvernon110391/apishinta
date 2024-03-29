<?php
namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\DetailCD;

class DetailCDTransformer extends TransformerAbstract {
    // default related transformable
    protected $defaultIncludes = [
        'detailSekunders',
        'kurs',
        'refHs',
        'refKemasan',
        'refSatuan'
    ];

    // just simple transform I guess
    public function transform(DetailCD $det) {
        return [
            'id'        => (int) $det->id,
            'uraian'    => $det->uraian,
            'satuan'    => [
                'jumlah'    => (int) $det->jumlah_satuan,
                'jenis'     => $det->jenis_satuan
            ],
            'kemasan'   => [
                'jumlah'    => (int) $det->jumlah_kemasan,
                'jenis'     => $det->jenis_kemasan
            ],
            // 'kode_valuta' => $det->kode_valuta,
            // 'nilai_valuta' => $det->nilai_valuta,
            'hscode'    => (string) $det->hs_code,
            'hsid'      => $det->hs_id,
            'fob'       => (float) $det->fob,
            'insurance' => (float) $det->insurance,
            'freight'   => (float) $det->freight,

            'ppnbm_tarif' => (float) $det->ppnbm_tarif,

            'brutto'    => (float) $det->brutto,
            'netto'     => (float) $det->netto,
            'cif'       => (float) $det->cif,
            'nilai_pabean'  => (float) $det->nilai_pabean,
            'kategori'  => $det->kategori_tags
        ];
    }

    // include data sekunder per detail
    public function includeDetailSekunders(DetailCD $det) {
        return $this->collection($det->detailSekunders, new DetailSekunderTransformer);
    }

    // include kurs data
    public function includeKurs(DetailCD $det) {
        return $this->item($det->kurs, new KursTransformer);
    }

    // include hs
    public function includeRefHs(DetailCD $det) {
        return $this->item($det->hs, new HsCodeTransformer);
    }

    // include kemasan
    public function includeRefKemasan(DetailCD $det) {
        return $this->item($det->kemasan, new KemasanTransformer);
    }

    // include satuan
    public function includeRefSatuan(DetailCD $det) {
        return $this->item($det->satuan, new SatuanTransformer);
    }
}

?>