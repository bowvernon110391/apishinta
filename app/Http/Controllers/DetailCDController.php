<?php

namespace App\Http\Controllers;

use App\DetailCD;
use App\DetailSekunder;
use App\Kategori;
use App\Kurs;
use App\Transformers\DetailCDTransformer;
use Illuminate\Http\Request;

class DetailCDController extends ApiController
{
    // grab one data
    public function show(Request $r, $id) {
        // try to grab based on its direct id
        $det = DetailCD::find($id);

        if (!$det) {
            return $this->errorNotFound("Detail CD #{$id} tidak ditemukan");
        }
        // it's found, respond accordingly
        return $this->respondWithItem($det, new DetailCDTransformer);
    }

    // update
    public function update(Request $r, $id) {
        // gotta be json
        if (!$r->isJson()) {
            return $this->errorBadRequest("Only json supported!");
        }
        // grab all essential data first
        $det = DetailCD::find($id);

        if (!$det) {
            return $this->errorNotFound("Detail CD #{$id} tidak ditemukan");
        }

        try {
            // grab all essential data, then attempt updating
            $uraian = expectSomething($r->get('uraian'), 'Uraian');
            $jumlah_satuan = expectSomething($r->get('jumlah_satuan'), 'Jumlah Satuan');
            $jenis_satuan = expectSomething($r->get('jenis_satuan'), 'Jenis Satuan');
            $jumlah_kemasan = expectSomething($r->get('jumlah_kemasan'), 'Jumlah Kemasan');
            $jenis_kemasan = expectSomething($r->get('jenis_kemasan'), 'Jenis Kemasan');
            $hs_code = expectSomething($r->get('hscode'), 'Kode HS');
            $fob = expectSomething($r->get('fob'), 'FOB');

            // kategori?
            // bisa kosong
            $kategori = $r->get('kategori');

            // detail sekunder?
            // bisa kosong
            $detailSekunder = $r->get('detailSekunder');

            // kurs? harus ada
            $kurs = expectSomething($r->get('kurs'), 'Kurs');

            // ok, set data
            $det->uraian = $uraian;
            $det->jumlah_satuan = $jumlah_satuan;
            $det->jumlah_kemasan = $jumlah_kemasan;
            $det->jenis_satuan = $jenis_satuan;
            $det->jenis_kemasan = $jenis_kemasan;
            $det->hs_code = $hs_code;
            $det->fob = $fob;

            // associate kurs
            $det->associate(Kurs::find($kurs->id));
            // try to associate shit? after saving
            $det->save();
            // kategori
            $det->sync(Kategori::byNameList($kategori));
            // detail sekunders?
            foreach ($detailSekunder as $ds) {
                // if it's got id, just update and save it
                if ($ds->id) {
                    // just update it
                    $detSekunder = DetailSekunder::findOrFail($ds->id);
                    $detSekunder->jenis = $ds->jenis;
                    $detSekunder->data = $ds->data;
                    // save
                    $detSekunder->save();
                } else {
                    // it's a new data, add it
                    $detSekunder = new DetailSekunder;
                    $detSekunder->jenis = $ds->jenis;
                    $detSekunder->data = $ds->data;
                    // save
                    $det->detailSekunders()->save($detSekunder);
                }
            }
            // say the good news
            return $this->setStatusCode(204)
                        ->respondWithEmptyBody();
        } catch (\Exception $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }

    // for creating one. quite long actually
    public function store(Request $r, $cdId) {
        
    }
}
