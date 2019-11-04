<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CD;
use App\DeclareFlag;
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
            $declare_flags  = $r->get('declare_flags');

            // pastikan id penumpang valid
            if (!Penumpang::find($penumpang_id)) {
                throw new \Exception("Penumpang dengan id {$penumpang_id} tidak ditemukan!");
            }

            $cd = new CD([
                'tgl_dok'   => $tgl_dok,
                'penumpang_id'    => $penumpang_id,
                'npwp_nib'    => $npwp_nib,
                'no_flight'    => $no_flight,
                'tgl_kedatangan'    => $tgl_kedatangan,
                'kd_pelabuhan_asal'    => $kd_pelabuhan_asal,
                'kd_pelabuhan_tujuan'    => $kd_pelabuhan_tujuan
            ]);

            // try save
            $cd->save();

            // sync flags and lokasi
            $cd->declareFlags()->sync(DeclareFlag::byName($declare_flags)->get());
            $cd->lokasi()->associate(Lokasi::byName($lokasi)->first());

            // return with array
            return $this->respondWithArray([
                'id'    => $cd->id,
                'uri'   => '/dokumen/cd/' . $cd->id
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
            //code...
            $cd->tgl_dok = expectSomething($r->get('tgl_dok'), 'Tanggal Dokumen');
            $cd->penumpang_id = expectSomething($r->get('penumpang_id'), 'Id Penumpang');
            $cd->npwp_nib = $r->get('npwp_nib');
            $cd->no_flight = expectSomething($r->get('no_flight'), 'Nomor flight');
            $cd->tgl_kedatangan = expectSomething($r->get('tgl_kedatangan'), 'Tanggal Kedatangan');
            $cd->kd_pelabuhan_asal = expectSomething($r->get('kd_pelabuhan_asal'), 'Kode Pelabuhan Asal');
            $cd->kd_pelabuhan_tujuan = expectSomething($r->get('kd_pelabuhan_tujuan'), 'Kode Pelabuhan Tujuan');
            
            $declare_flags  = $r->get('declare_flags');
            $lokasi = expectSomething($r->get('lokasi'), 'Lokasi');


            // pastikan id penumpang valid
            if (!Penumpang::find($cd->penumpang_id)) {
                throw new \Exception("Penumpang dengan id {$cd->penumpang_id} tidak ditemukan!");
            }
            
            // try to save
            $cd->save();
            $cd->declareFlags()->sync(DeclareFlag::byName($declare_flags)->get());
            $cd->lokasi()->associate(Lokasi::byName($lokasi)->first());
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
    public function destroy($id)
    {
        // 
    }
}
