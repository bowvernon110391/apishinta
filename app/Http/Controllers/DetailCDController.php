<?php

namespace App\Http\Controllers;

use App\CD;
use App\DetailCD;
use App\DetailSekunder;
use App\Kategori;
use App\Kurs;
use App\ReferensiJenisDetailSekunder;
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
            $brutto = expectSomething($r->get('brutto'), 'Brutto');
            $netto = expectSomething($r->get('netto'), 'Netto');

            // kategori?
            // bisa kosong
            $kategori = $r->get('kategori');

            // detail sekunder?
            // bisa kosong
            $detailSekunders = $r->get('detailSekunders');

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
            $det->brutto = $brutto;
            $det->netto = $netto;

            // associate kurs
            $det->kurs()->associate(Kurs::find($kurs['id']));
            // try to associate shit? after saving
            $det->save();
            // kategori
            $det->kategoris()->sync(Kategori::byNameList($kategori)->get());
            // detail sekunders?
            // delete all, reinsert then
            $det->detailSekunders()->delete();
            // reinsert
            foreach ($detailSekunders as $ds) {
                // if it's got id, just update and save it
                if (1/* $ds['id'] */) {
                    /* // just update it
                    $detSekunder = new DetailSekunder;
                    $detSekunder->id = $ds['id'];
                    $detSekunder->referensiJenisDetailSekunder()->associate(ReferensiJenisDetailSekunder::byName($ds['jenis'])->first());
                    $detSekunder->data = $ds['data'];
                    // save
                    $detSekunder->save();
                } else { */
                    // it's a new data, add it
                    $detSekunder = new DetailSekunder;
                    $detSekunder->id = $ds['id'];
                    $detSekunder->referensiJenisDetailSekunder()->associate(ReferensiJenisDetailSekunder::byName($ds['jenis'])->first());
                    $detSekunder->data = $ds['data'];
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
        // gotta be json
        if (!$r->isJson()) {
            return $this->errorBadRequest("Only json supported!");
        }
        // grab header
        $cd = CD::find($cdId);
        
        if (!$cd) {
            return $this->errorNotFound("CD #{$cdId} tidak ditemukan");
        }

        // grab all essential data first
        try {
            // grab all essential data, then attempt updating
            $uraian = expectSomething($r->get('uraian'), 'Uraian');
            $jumlah_satuan = expectSomething($r->get('jumlah_satuan'), 'Jumlah Satuan');
            $jenis_satuan = expectSomething($r->get('jenis_satuan'), 'Jenis Satuan');
            $jumlah_kemasan = expectSomething($r->get('jumlah_kemasan'), 'Jumlah Kemasan');
            $jenis_kemasan = expectSomething($r->get('jenis_kemasan'), 'Jenis Kemasan');
            $hs_code = expectSomething($r->get('hscode'), 'Kode HS');
            $fob = expectSomething($r->get('fob'), 'FOB');
            $brutto = expectSomething($r->get('brutto'), 'Brutto');
            $netto = expectSomething($r->get('netto'), 'Netto');

            // kategori?
            // bisa kosong
            $kategori = $r->get('kategori');

            // detail sekunder?
            // bisa kosong
            $detailSekunders = $r->get('detailSekunders');

            // kurs? harus ada
            $kurs = expectSomething($r->get('kurs'), 'Kurs');

            // ok, set data
            $det = new DetailCD([
                'uraian'    => $uraian,
                'jumlah_satuan' => $jumlah_satuan,
                'jumlah_kemasan' => $jumlah_kemasan,
                'jenis_satuan' => $jenis_satuan,
                'jenis_kemasan' => $jenis_kemasan,
                'hs_code' => $hs_code,
                'fob' => $fob,
                'brutto' => $brutto,
                'netto' => $netto
            ]);

            // associate kurs
            $det->kurs()->associate(Kurs::find($kurs['id']));

            $det = $cd->details()->save($det);
            // $det->header()->associate($cd);

            // try to associate shit? after saving
            // $cd->details()->save($det);
            // $det->save();
            // kategori
            $det->kategoris()->sync(Kategori::byNameList($kategori)->get());
            // detail sekunders?
            foreach ($detailSekunders as $ds) {
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
            // if successfull, send out info
            return $this->respondWithArray([
                'id'    => $det->id,
                'uri'   => '/cd/details/' . $det->id
            ]);
        } catch (\Exception $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }

    // deleting stuffs is easy
    public function destroy(Request $r, $id) {
        // might wanna check if user is allowed to do that
        // or if the header is not locked out yet
        try {
            // does it exist?
            $det = DetailCD::find($id);

            if (!$det) {
                throw new \Exception("DetailCD #{$id} tidak ditemukan");
            }
            // it exists, can we delete?
            if (!canEdit($det->header->is_locked, $r->userInfo)) {
                throw new \Exception("Dokumen sudah terkunci atau anda tidak memiliki privilege yang cukup untuk menghapus detail ini");
            }
            // ok, delete
            $det->delete();
            // say it's done
            return $this->setStatusCode(204)
                        ->respondWithEmptyBody();
        } catch (\Exception $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }
}
