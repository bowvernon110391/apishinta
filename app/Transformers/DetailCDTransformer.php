<?php
namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\DetailCD;

class DetailCDTransformer extends TransformerAbstract {
    // default related transformable
    protected $defaultIncludes = [
        'detailSekunders',
        'kurs'
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
            'kode_valuta' => $det->kode_valuta,
            'nilai_valuta' => $det->nilai_valuta,
            'hscode'    => (string) $det->hs_code,
            'fob'       => (float) $det->fob,
            'insurance' => (float) $det->insurance,
            'freight'   => (float) $det->freight,
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
}

?>