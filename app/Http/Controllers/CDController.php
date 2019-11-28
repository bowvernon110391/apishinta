<?php

namespace App\Http\Controllers;

use App\AppLog;
use Illuminate\Http\Request;
use App\CD;
use App\DeclareFlag;
use App\Kurs;
use App\Lokasi;
use App\Penumpang;
use App\Transformers\CDTransformer;
use App\Transformers\DetailCDTransformer;
use Exception;

class CDController extends ApiController
{
    /**
     * Display a listing of Customs Declaration, possibly with query strings for custom query
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = CD::byQuery(
            $request->get('q', ''),
            $request->get('from'),
            $request->get('to')
        );

        $paginator = $query
                    ->paginate($request->get('number'))
                    ->appends($request->except('page'));
        
        return $this->respondWithPagination($paginator, new CDTransformer);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $r)
    {
        // validasi dlu
        try {
            // tgl dok
            $tgl_dok = expectSomething($r->get('tgl_dok'), 'Tanggal Dokumen');
            $lokasi = expectSomething($r->get('lokasi'), 'Lokasi');
            $penumpang_id = expectSomething($r->get('penumpang_id'), 'Id Penumpang');
            $npwp_nib = $r->get('npwp_nib');
            $no_flight = expectSomething($r->get('no_flight'), 'Nomor flight');
            $tgl_kedatangan = expectSomething($r->get('tgl_kedatangan'), 'Tanggal Kedatangan');
            $kd_pelabuhan_asal = expectSomething($r->get('kd_pelabuhan_asal'), 'Kode Pelabuhan Asal');
            $kd_pelabuhan_tujuan = expectSomething($r->get('kd_pelabuhan_tujuan'), 'Kode Pelabuhan Tujuan');
            $alamat = expectSomething($r->get('alamat'), 'Alamat Tinggal');
            $declare_flags  = $r->get('declare_flags');

            // keluarga, pembebasan
            $jml_bagasi_dibawa = expectSomething($r->get('jml_bagasi_dibawa'), 'Jumlah Bagasi Dibawa');
            $jml_bagasi_tdk_dibawa = expectSomething($r->get('jml_bagasi_tdk_dibawa'), 'Jumlah Bagasi Tidak Dibawa');
            $pembebasan = expectSomething($r->get('pembebasan'), 'Jumlah Pembebasan');
            $jml_anggota_keluarga = expectSomething($r->get('jml_anggota_keluarga'), 'Jumlah Anggota Keluarga');
            $pph_tarif = expectSomething($r->get('pph_tarif'), 'Tarif PPh');
            $ndpbm = expectSomething($r->get('ndpbm'), 'NDPBM');

            // pastikan id penumpang valid
            if (!Penumpang::find($penumpang_id)) {
                throw new \Exception("Penumpang dengan id {$penumpang_id} tidak ditemukan!");
            }

            $cd = new CD([
                'tgl_dok'   => $tgl_dok,
                'penumpang_id'    => $penumpang_id,
                'no_flight'    => $no_flight,
                'tgl_kedatangan'    => $tgl_kedatangan,
                'kd_pelabuhan_asal'    => $kd_pelabuhan_asal,
                'kd_pelabuhan_tujuan'    => $kd_pelabuhan_tujuan,
                'alamat'    => $alamat,
                'jml_bagasi_dibawa'     => $jml_bagasi_dibawa,
                'jml_bagasi_tdk_dibawa' => $jml_bagasi_tdk_dibawa,
                'pembebasan'    => $pembebasan,
                'jml_anggota_keluarga'  => $jml_anggota_keluarga,
                'pph_tarif'     => $pph_tarif
            ]);

            // set npwp/nib
            if ($npwp_nib) {
                $cd->npwp = $cd->nib = $npwp_nib;
            }

            // associate lokasi first
            $cd->lokasi()->associate(Lokasi::byName($lokasi)->first());

            // ndpbm
            $kursNdpbm = Kurs::find($ndpbm['data']['id']);

            // if not found, refuse further processing
            if (!$kursNdpbm) {
                throw new \Exception("Kurs NDPBM invalid");
            }

            $cd->ndpbm()->associate($kursNdpbm);

            // try save first
            $cd->save();

            // sync flags and lokasi
            $cd->declareFlags()->sync(DeclareFlag::byName($declare_flags)->get());

            // log it
            AppLog::logInfo("CD #{$cd->id} diinput oleh {$r->userInfo['username']}", $cd);

            // return with array
            return $this->respondWithArray([
                'id'    => $cd->id,
                'uri'   => '/cd/' . $cd->id
            ]);
        } catch (\Exception $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }

    /**
     * Display Customs Declaration Header Data 
     * Jumlah detail gk dimunculin krn kemungkinan besar dan butuh paginasi
     * 
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        // $this->fractal->parseIncludes($request->get('include', ''));

        $cd = CD::find($id);

        if (!$cd) {
            return $this->errorNotFound("Gak nemu data CD dengan id {$id}");
        }

        return $this->respondWithItem($cd, new CDTransformer);
    }

    /**
     * Display the details of a Customs Declaration
     * with pagination
     */
    public function showDetails(Request $request, $id) {
        $cd = CD::find($id);

        if (!$cd) {
            return $this->errorNotFound("Gak nemu data CD dengan id {$id}");
        }

        // mungkin kah gk ada CD detailsnya?
        $paginator = $cd->details()
                    ->paginate($request->get('number', 10))
                    ->appends($request->except('page'));

        return $this->respondWithPagination($paginator, new DetailCDTransformer);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $r, $id)
    {
        // update data
        try {
            // first, grab cd
            $cd = CD::find($id);
            if (!$cd) {
                throw new \Exception("CD dengan id {$id} tidak ditemukan");
            }
            // check if it's locked
            // UPDATE: CHECK IF USER CAN UPDATE
            if (!canEdit($cd->is_locked, $r->userInfo)) {
                throw new \Exception("Dokumen ini sudah terkunci, kontak administrator untuk informasi lebih lanjut");
            }
            //code...
            $cd->tgl_dok = expectSomething($r->get('tgl_dok'), 'Tanggal Dokumen');
            $cd->penumpang_id = expectSomething($r->get('penumpang_id'), 'Id Penumpang');
            $cd->no_flight = expectSomething($r->get('no_flight'), 'Nomor flight');
            $cd->tgl_kedatangan = expectSomething($r->get('tgl_kedatangan'), 'Tanggal Kedatangan');
            $cd->kd_pelabuhan_asal = expectSomething($r->get('kd_pelabuhan_asal'), 'Kode Pelabuhan Asal');
            $cd->kd_pelabuhan_tujuan = expectSomething($r->get('kd_pelabuhan_tujuan'), 'Kode Pelabuhan Tujuan');
            $cd->alamat = expectSomething($r->get('alamat'), 'Alamat/Domisili');

            // keluarga, pembebasan
            $cd->jml_bagasi_dibawa = expectSomething($r->get('jml_bagasi_dibawa'), 'Jumlah Bagasi Dibawa');
            $cd->jml_bagasi_tdk_dibawa = expectSomething($r->get('jml_bagasi_tdk_dibawa'), 'Jumlah Bagasi Tidak Dibawa');
            $cd->pembebasan = expectSomething($r->get('pembebasan'), 'Jumlah Pembebasan');
            $cd->jml_anggota_keluarga = expectSomething($r->get('jml_anggota_keluarga'), 'Jumlah Anggota Keluarga');
            $cd->pph_tarif = expectSomething($r->get('pph_tarif'), 'Tarif PPh');
            
            $declare_flags  = $r->get('declare_flags');
            $lokasi = expectSomething($r->get('lokasi'), 'Lokasi');
            $npwp_nib = $r->get('npwp_nib');
            $ndpbm = expectSomething($r->get('ndpbm'), 'NDPBM');

            // set npwp/nib
            if ($npwp_nib) {
                $cd->npwp = $cd->nib = $npwp_nib;
            }

            // pastikan id penumpang valid
            if (!Penumpang::find($cd->penumpang_id)) {
                throw new \Exception("Penumpang dengan id {$cd->penumpang_id} tidak ditemukan!");
            }

            // $cd->ndpbm_id = $ndpbm['data']['id'];

            // ndpbm
            $kursNdpbm = Kurs::find($ndpbm['data']['id']);

            // if not found, refuse further processing
            if (!$kursNdpbm) {
                throw new \Exception("Kurs NDPBM invalid");
            }

            $cd->ndpbm()->associate($kursNdpbm);
            $cd->lokasi()->associate(Lokasi::byName($lokasi)->first());
            $cd->declareFlags()->sync(DeclareFlag::byName($declare_flags)->get());
            
            // try to save
            $cd->save();
            
            

            // log it
            AppLog::logInfo("CD #{$cd->id} diupdate oleh {$r->userInfo['username']}", $cd);

            // return no body
            return $this->setStatusCode(200)->respondWithEmptyBody();
        } catch (\Exception $e) {
            //throw $th;
            return $this->errorBadRequest($e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $r, $id)
    {
        // first, find it
        $cd = CD::find($id);

        if (!$cd) {
            return $this->errorNotFound("CD #{$id} tidak ditemukan");
        }

        // may we delete it?
        if (!canEdit($cd->is_locked, $r->userInfo)) {
            return $this->errorForbidden("Dokumen sudah terkunci dan anda tidak memiliki hak untuk melakukan penghapusan ini");
        }

        // attempt deletion
        try {
            $cd->delete();

            return $this->setStatusCode(204)
                        ->respondWithEmptyBody();
        } catch (\Exception $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }

    /**
     * Simulasi perhitungan pungutan
     */
    public function simulasiHitung($id) {
        // cari cd
        $cd = CD::find($id);

        if (!$cd) {
            return $this->errorNotFound("CD #{$id} tidak ditemukan");
        }

        try {
            $pungutan = $cd->simulasi_pungutan;
            return $this->respondWithArray($pungutan);
        } catch (\Exception $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }
}
