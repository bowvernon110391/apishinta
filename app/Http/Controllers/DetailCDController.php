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

        // may we edit shit?
        if (!canEdit($det->header->is_locked, $r->userInfo)) {
            return $this->errorForbidden("Dokumen sudah terkunci dan anda tidak berhak melakukan proses ini");
        }

        try {
            $data = $r->json()->all();
            // grab all essential data, then attempt updating
            $uraian = expectSomething($data['uraian'], 'Uraian');

            $jumlah_satuan = $data['satuan']['jumlah'] ?? 0; // expectSomething($data['satuan']['jumlah'], 'Jumlah Satuan');
            $jenis_satuan = $data['satuan']['jenis'] ?? 'PCE'; // expectSomething($data['satuan']['jenis'], 'Jenis Satuan');

            $jumlah_kemasan = expectSomething($data['kemasan']['jumlah'], 'Jumlah Kemasan');
            $jenis_kemasan = expectSomething($data['kemasan']['jenis'], 'Jenis Kemasan');
            
            // $hs_code = expectSomething($data['hscode'], 'Kode HS');
            $hs_id = expectSomething($data['hsid'], 'Kode HS');
            $fob = expectSomething($data['fob'], 'FOB');
            $brutto = expectSomething($data['brutto'], 'Brutto');
            $netto = $data['netto']; //expectSomething($data['netto'], 'Netto');
            $freight = $r->get('freight', 0);
            $insurance = $r->get('insurance', 0);
            $ppnbm_tarif = $r->get('ppnbm_tarif');

            // kategori?
            // bisa kosong
            $kategori = $data['kategori'];

            // detail sekunder?
            // bisa kosong
            $detailSekunders = $data['detailSekunders']['data'];

            // kurs? harus ada
            $kurs = expectSomething($data['kurs']['data'], 'Kurs');

            // ok, set data
            $det->uraian = $uraian;
            $det->jumlah_satuan = $jumlah_satuan;
            $det->jumlah_kemasan = $jumlah_kemasan;
            $det->jenis_satuan = $jenis_satuan;
            $det->jenis_kemasan = $jenis_kemasan;
            // $det->hs_code = $hs_code;
            $det->hs_id = $hs_id;
            $det->fob = $fob;
            $det->brutto = $brutto;
            $det->netto = $netto;
            $det->freight = $freight;
            $det->insurance = $insurance;
            $det->ppnbm_tarif = $ppnbm_tarif;

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
            if ($detailSekunders) {
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

        // may we edit shit?
        if (!canEdit($cd->is_locked, $r->userInfo)) {
            return $this->errorForbidden("Dokumen sudah terkunci dan anda tidak berhak melakukan proses ini");
        }

        // grab all essential data first
        try {
            $data = $r->json()->all();
            // grab all essential data, then attempt updating
            $uraian = expectSomething($data['uraian'], 'Uraian');
            $jumlah_satuan = $data['satuan']['jumlah'] ?? 0; //expectSomething($data['satuan']['jumlah'], 'Jumlah Satuan');
            $jenis_satuan = $data['satuan']['jenis'] ?? 'PCE'; //expectSomething($data['satuan']['jenis'], 'Jenis Satuan');
            $jumlah_kemasan = expectSomething($data['kemasan']['jumlah'], 'Jumlah Kemasan');
            $jenis_kemasan = expectSomething($data['kemasan']['jenis'], 'Jenis Kemasan');
            // $hs_code = expectSomething($data['hscode'], 'Kode HS');
            $hs_id = expectSomething($data['hsid'], 'Kode HS');
            $fob = expectSomething($data['fob'], 'FOB');
            $brutto = expectSomething($data['brutto'], 'Brutto');
            $netto = $data['netto']; //expectSomething($data['netto'], 'Netto');
            $freight = $r->get('freight', 0);
            $insurance = $r->get('insurance', 0);
            $ppnbm_tarif = $r->get('ppnbm_tarif');

            // kategori?
            // bisa kosong
            $kategori = $data['kategori'];

            // detail sekunder?
            // bisa kosong
            $detailSekunders = $data['detailSekunders']['data'];

            // kurs? harus ada
            $kurs = expectSomething($data['kurs']['data'], 'Kurs');

            // ok, set data
            $det = new DetailCD([
                'uraian'    => $uraian,
                'jumlah_satuan' => $jumlah_satuan,
                'jumlah_kemasan' => $jumlah_kemasan,
                'jenis_satuan' => $jenis_satuan,
                'jenis_kemasan' => $jenis_kemasan,
                // 'hs_code' => $hs_code,
                'hs_id' => $hs_id,
                'fob' => $fob,
                'brutto' => $brutto,
                'netto' => $netto,
                'freight'   => $freight,
                'insurance' => $insurance,
                'ppnbm_tarif' => $ppnbm_tarif
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
        // it exists, can we delete?
        // does it exist?
        $det = DetailCD::find($id);

        if (!$det) {
            return $this->errorNotFound("DetailCD #{$id} tidak ditemukan");
        }

        if (!canEdit($det->header->is_locked, $r->userInfo)) {
            return $this->errorForbidden("Dokumen sudah terkunci dan anda tidak berhak melakukan proses ini");
        }

        try {
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
