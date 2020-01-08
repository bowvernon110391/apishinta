<?php

namespace App\Http\Controllers;

use App\BPJ;
use App\Transformers\BPJTransformer;
use Illuminate\Http\Request;

class BPJController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $r)
    {
        // build query
        $query = BPJ::byQuery(
            $r->get('q', ''),
            $r->get('from'),
            $r->get('to')
        );

        $paginator = $query
                    ->paginate($r->get('number'))
                    ->appends($r->except('page'));
        
        return $this->respondWithPagination($paginator, new BPJTransformer);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // simpan BPJ
        // elemen input
        // $b->penumpang()->associate(App\Penumpang::inRandomOrder()->first());
        // // associate lokasi
        // $b->lokasi()->associate(App\Lokasi::inRandomOrder()->first());

        // $ts = $faker->dateTimeBetween('-2 months')->getTimestamp();
        // $b->tgl_dok = date('Y-m-d', $ts);
        // // $b->no_dok = '';

        // $b->jenis_identitas = 'PASPOR';
        // $b->no_identitas = $b->penumpang->no_paspor;
        // $b->alamat = $faker->address;

        // $b->nomor_jaminan = random_int(10, 8929);
        // $b->tanggal_jaminan = date('Y-m-d', $faker->dateTimeBetween('-2 months')->getTimestamp());

        // $b->penjamin = $faker->company;
        // $b->alamat_penjamin = $faker->address;
        // $b->bentuk_jaminan = 'TUNAI';
        
        // $b->jumlah = $faker->randomFloat(-3, 125000, 15000000);
        // $b->jenis = 'TUNAI';
        // $b->tanggal_jatuh_tempo = date('Y-m-d', $faker->dateTimeBetween('+1 months', '+2 months')->getTimestamp());

        // $b->nip_pembuat = $faker->numerify("##################");
        // $b->nama_pembuat = $faker->name;

        // $b->active = true;
        // $b->status = 'AKTIF';
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // show individual BPJ
        $bpj = BPJ::find($id);

        if (!$bpj) {
            return $this->errorNotFound("BPJ dengan id #{$id} tidak ditemukan.");
        }

        return $this->respondWithItem($bpj, new BPJTransformer);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $r, $id)
    {
        //  find it
        $bpj = BPJ::find($id);

        if (!$bpj) {
            return $this->errorNotFound("BPJ #{$id} tidak ditemukan.");
        }

        // forbid if user is unauthorized
        if (!canEdit($bpj->is_locked, $r->userInfo)) {
            return $this->errorForbidden("BPJ sudah terpakai, tidak dapat dibatalkan.");
        }
    }
}
