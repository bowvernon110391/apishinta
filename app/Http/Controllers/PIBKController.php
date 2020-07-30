<?php

namespace App\Http\Controllers;

use App\AppLog;
use App\BPPM;
use App\Kurs;
use App\Lock;
use App\PIBK;
use App\Pungutan;
use App\ReferensiJenisPungutan;
use App\SSOUserCache;
use App\Transformers\DetailBarangTransformer;
use App\Transformers\PIBKTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PIBKController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $r)
    {
        // grab a query and return shiets
        $q = $r->get('q', '');
        $from = $r->get('from');
        $to = $r->get('to');

        $query = PIBK::byQuery($q, $from, $to);

        $paginator = $query->paginate($r->get('number'))
                            ->appends($r->except('page'));

        return $this->respondWithPagination($paginator, new PIBKTransformer);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $r)
    {
        // welp, let's save some pibk header!!
        // use transaction
        DB::beginTransaction();

        try {
            // grab inputs
            $tgl_dok = expectSomething($r->get('tgl_dok'), 'Tanggal Dokumen');

            $npwp = $r->get('npwp') ?? '';
            $importir_type = expectSomething($r->get('importir_type'), 'Jenis Importir');
            $importir_id = expectSomething($r->get('importir_id'), 'ID Importir');
            $alamat = $r->get('alamat') ?? '';

            $pemberitahu_type = $r->get('pemberitahu_type');
            $pemberitahu_id = $r->get('pemberitahu_id');

            $no_flight = expectSomething($r->get('no_flight'), 'Nomor Flight');
            $kd_airline = expectSomething($r->get('kd_airline'), 'Kode Airline');
            $tgl_kedatangan = expectSomething($r->get('tgl_kedatangan'), 'Tanggal Kedatangan');

            $kd_pelabuhan_asal = expectSomething($r->get('kd_pelabuhan_asal'), 'Kode Pelabuhan Asal');
            $kd_pelabuhan_tujuan = expectSomething($r->get('kd_pelabuhan_tujuan'), 'Kode Pelabuhan Tujuan');

            $no_bc11 = $r->get('no_bc11');
            $tgl_bc11 = $r->get('tgl_bc11');
            $pos_bc11 = $r->get('pos_bc11');
            $subpos_bc11 = $r->get('subpos_bc11');
            $subsubpos_bc11 = $r->get('subsubpos_bc11');

            $pph_tarif = expectSomething($r->get('pph_tarif'), "Tarif PPh");

            $lokasi_type = expectSomething($r->get('lokasi_type'), "Jenis Lokasi Barang");
            $lokasi_id = expectSomething($r->get('lokasi_id'), "ID Lokasi Barang");

            // force kode airline
            if (strlen($kd_airline) < 2 && strlen($no_flight) < 5) {
                throw new \Exception("Data penerbangan tidak valid. Cek kembali nomor flightnya");
            } else if (strlen($kd_airline) < 2) {
                $kd_airline = strtoupper(substr($no_flight, 0, 2));
            }

            // try to store it (lazy, the db will generate exception if something is bogus anyway)
            $pibk = new PIBK([
                'tgl_dok' => $tgl_dok,
                'npwp' => $npwp,
                'importir_type' => $importir_type,
                'importir_id' => $importir_id,
                'pemberitahu_type' => $pemberitahu_type,
                'pemberitahu_id' => $pemberitahu_id,
                'alamat' => $alamat,
                'no_flight' => $no_flight,
                'kd_airline' => $kd_airline,
                'tgl_kedatangan' => $tgl_kedatangan,
                'kd_pelabuhan_asal' => $kd_pelabuhan_asal,
                'kd_pelabuhan_tujuan' => $kd_pelabuhan_tujuan,
                'no_bc11' => strlen($no_bc11) ? $no_bc11 : null,
                'tgl_bc11' => strlen($tgl_bc11) ? $tgl_bc11 : null,
                'pos_bc11' => strlen($pos_bc11) ? $pos_bc11 : null,
                'subpos_bc11' => strlen($subpos_bc11) ? $subpos_bc11 : null,
                'subsubpos_bc11' => strlen($subsubpos_bc11) ? $subsubpos_bc11 : null,
                'pph_tarif' => $pph_tarif,
                'lokasi_type' => $lokasi_type,
                'lokasi_id' => $lokasi_id
            ]);

            // gotta grab kurs
            $ndpbm = Kurs::perTanggal(date('Y-m-d'))->kode('USD')->first();

            if (!$ndpbm) {
                throw new \Exception("Kurs NDPBM invalid. Pastikan data kurs up-to-date");
            }

            $pibk->ndpbm()->associate($ndpbm);

            // save it
            $pibk->save();

            // sync dokkap 
            $pibk->syncDokkap($r->get('dokkap')['data']);

            // log it
            AppLog::logInfo("PIBK #{$pibk->id} diinput oleh {$r->userInfo['username']}", false);

            // append created status
            $pibk->appendStatus('CREATED');

            // commit
            DB::commit();

            // return something
            return $this->respondWithArray([
                'id' => $pibk->id,
                'uri' => '/pibk/' . $pibk->id
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->errorBadRequest($e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // show one PIBK
        try {
            $p = PIBK::findOrFail($id);

            return $this->respondWithItem($p, new PIBKTransformer);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound("PIBK #{$id} was not found");
        } catch (\Throwable $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }

    /**
     * Show detail barang of a pibk
     */
    public function showDetails(Request $r, $id) {
        $pibk = PIBK::find($id);

        if (!$pibk) {
            return $this->errorNotFound("PIBK #{$id} was not found");
        }

        // grab details
        $paginator = $pibk->detailBarang()/* ->isPenetapan() */
                    ->paginate($r->get('number', 10))
                    ->appends($r->except('page'));
        
        return $this->respondWithPagination($paginator, new DetailBarangTransformer);
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
        // update stuffs. to do later
        DB::beginTransaction();

        try {
            // grab data
            $pibk = PIBK::findOrFail($id);

            // can we edit
            if (!canEdit($pibk->is_locked, $r->userInfo)) {
                throw new \Exception("Dokumen ini sudah terkunci, kontak administrator untuk informasi lebih lanjut");
            }

            // grab inputs
            $tgl_dok = expectSomething($r->get('tgl_dok'), 'Tanggal Dokumen');

            $npwp = $r->get('npwp') ?? '';
            $importir_type = expectSomething($r->get('importir_type'), 'Jenis Importir');
            $importir_id = expectSomething($r->get('importir_id'), 'ID Importir');
            $alamat = $r->get('alamat') ?? '';

            $pemberitahu_type = $r->get('pemberitahu_type');
            $pemberitahu_id = $r->get('pemberitahu_id');

            $no_flight = expectSomething($r->get('no_flight'), 'Nomor Flight');
            $kd_airline = expectSomething($r->get('kd_airline'), 'Kode Airline');
            $tgl_kedatangan = expectSomething($r->get('tgl_kedatangan'), 'Tanggal Kedatangan');

            $kd_pelabuhan_asal = expectSomething($r->get('kd_pelabuhan_asal'), 'Kode Pelabuhan Asal');
            $kd_pelabuhan_tujuan = expectSomething($r->get('kd_pelabuhan_tujuan'), 'Kode Pelabuhan Tujuan');

            $no_bc11 = $r->get('no_bc11');
            $tgl_bc11 = $r->get('tgl_bc11');
            $pos_bc11 = $r->get('pos_bc11');
            $subpos_bc11 = $r->get('subpos_bc11');
            $subsubpos_bc11 = $r->get('subsubpos_bc11');

            $pph_tarif = expectSomething($r->get('pph_tarif'), "Tarif PPh");

            $lokasi_type = expectSomething($r->get('lokasi_type'), "Jenis Lokasi Barang");
            $lokasi_id = expectSomething($r->get('lokasi_id'), "ID Lokasi Barang");

            // force kode airline
            if (strlen($kd_airline) < 2 && strlen($no_flight) < 5) {
                throw new \Exception("Data penerbangan tidak valid. Cek kembali nomor flightnya");
            } else if (strlen($kd_airline) < 2) {
                $kd_airline = strtoupper(substr($no_flight, 0, 2));
            }

            // set data
            $pibk->tgl_dok = $tgl_dok;
            $pibk->npwp = $npwp;
            $pibk->importir_type = $importir_type;
            $pibk->importir_id = $importir_id;
            $pibk->pemberitahu_type = $pemberitahu_type;
            $pibk->pemberitahu_id = $pemberitahu_id;
            $pibk->alamat = $alamat;
            $pibk->no_flight = $no_flight;
            $pibk->kd_airline = $kd_airline;
            $pibk->tgl_kedatangan = $tgl_kedatangan;
            $pibk->kd_pelabuhan_asal = $kd_pelabuhan_asal;
            $pibk->kd_pelabuhan_tujuan = $kd_pelabuhan_tujuan;
            $pibk->no_bc11 = strlen($no_bc11) ? $no_bc11 : null;
            $pibk->tgl_bc11 = strlen($tgl_bc11) ? $tgl_bc11 : null;
            $pibk->pos_bc11 = strlen($pos_bc11) ? $pos_bc11 : null;
            $pibk->subpos_bc11 = strlen($subpos_bc11) ? $subpos_bc11 : null;
            $pibk->subsubpos_bc11 = strlen($subsubpos_bc11) ? $subsubpos_bc11 : null;
            $pibk->pph_tarif = $pph_tarif;
            $pibk->lokasi_type = $lokasi_type;
            $pibk->lokasi_id = $lokasi_id;

            // gotta grab kurs
            $ndpbm = Kurs::perTanggal(date('Y-m-d'))->kode('USD')->first();

            if (!$ndpbm) {
                throw new \Exception("Kurs NDPBM invalid. Pastikan data kurs up-to-date");
            }

            $pibk->ndpbm()->associate($ndpbm);

            // save it
            $pibk->save();

            // sync dokkap 
            $pibk->syncDokkap($r->get('dokkap')['data']);

            // log it
            AppLog::logInfo("PIBK #{$pibk->id} diupdate oleh {$r->userInfo['username']}", $pibk, false);

            // commit
            DB::commit();

            // return something
            return $this->setStatusCode(204)
                        ->respondWithEmptyBody();
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return $this->errorNotFound("PIBK #{$id} was not found");
        } catch (\Throwable $e) {
            DB::rollBack();
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
        // show one PIBK
        try {
            $p = PIBK::findOrFail($id);

            if (!canEdit($p->is_locked, $r->userInfo)) {
                throw new \Exception("PIBK #{$id} is locked and you don't have enough privilege to bypass it");
            }

            $p->delete();

            return $this->setStatusCode(204)
                    ->respondWithEmptyBody();
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound("PIBK #{$id} was not found");
        } catch (\Throwable $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }

    /**
     * Return computed pungutan
     */
    public function simulasiHitung(Request $r, $id) {
        $p = PIBK::find($id);
        
        if (!$p) {
            return $this->errorNotFound("PIBK #{$id} was not found");
        }

        try {
            $pungutan = $p->computePungutanImpor();
            $keterangan = $p->keterangan()->first();

            return $this->respondWithArray([
                'pungutan' => $pungutan,
                'keterangan' => $keterangan
            ]);
        } catch (\Throwable $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }

    /**
     * store said pungutan
     */
    public function storePenetapan(Request $r, $id) {
        // Use transaction
        DB::beginTransaction();
        try {
            // grab pibk
            $pibk = PIBK::findOrFail($id);

            // is it locked?
            if ($pibk->is_locked) {
                throw new \Exception("PIBK sudah terkunci!");
            }

            // grab pungutan
            $data = $pibk->computePungutanImpor();

            $pungutan = $data['pungutan'];

            if (!count($pungutan)) {
                throw new \Exception("Tidak ada pungutan untuk PIBK ini! cek kembali datanya");
            }

            // let's check total of them
            $bayar = array_reduce($pungutan['bayar'], function($acc, $e) { return $acc + $e; }, 0);
            $bebas = array_reduce($pungutan['bebas'], function($acc, $e) { return $acc + $e; }, 0);
            $tunda = array_reduce($pungutan['tunda'], function($acc, $e) { return $acc + $e; }, 0);
            $tanggung_pemerintah = array_reduce($pungutan['tanggung_pemerintah'], function($acc, $e) { return $acc + $e; }, 0);

            // makes sense gk hasil perhitungannya?
            if ($bayar < 1000.0) {
                throw new \Exception("Total Pungutan Bayar < Rp 1000!, computed: {$bayar}" );
            }

            // bebas, tunda, tanggung pemerintah harus 0
            if ($bebas + $tunda + $tanggung_pemerintah > 0) {
                throw new \Exception("Penetapan PIBK harus bayar full, computed => bebas: {$bebas}, tunda: {$tunda}, tanggung_pemerintah: {$tanggung_pemerintah}");
            }

            // grab new data
            $pungutan = $pungutan['bayar'];

            // #1, SPAWN PUNGUTAN
            foreach ($pungutan as $kode => $jmlPungutan) {
                $refJenis = ReferensiJenisPungutan::byKode($kode)->first();

                if (!$refJenis) {
                    throw new \Exception("Jenis Pungutan '{$kode}' tidak terdaftar di sistem!");
                }

                $p = new Pungutan([
                    'bayar' => $jmlPungutan,
                    'bebas' => 0,
                    'tunda' => 0,
                    'tanggung_pemerintah' => 0
                ]);


                $pejabat = SSOUserCache::byId($r->userInfo['user_id']);
                $p->pejabat()->associate($pejabat);
                $p->dutiable()->associate($pibk);
                $p->jenisPungutan()->associate($refJenis);

                $p->save();
            }

            // #2, KETERANGAN
            $pibk->keterangan()->create([
                'keterangan' => $r->get('keterangan', '') ?? ''
            ]);

            // #3, LOCK
            $l = new Lock([
                'keterangan' => "Penetapan pungutan PIBK"
            ]);

            
            $l->petugas()->associate(SSOUserCache::byId($r->userInfo['user_id']));
            $pibk->lock()->save($l);
            
            // #4, APPEND STATUS
            $pibk->appendStatus(
                'PENETAPAN',
                null,
                "Penetapan Pungutan atas PIBK",
                null,
                null,
                $l->petugas
            );
            
            // #5, LOG IT
            AppLog::logInfo("PIBK #{$id} ditetapkan pungutannya oleh {$r->userInfo['username']}", $pibk, false);

            // #6, SET NOMOR DOKUMEN
            $pibk->setNomorDokumen();

            // commit
            DB::commit();

            // just return 204
            return $this->setStatusCode(204)
                        ->respondWithEmptyBody();
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return $this->errorNotFound("PIBK #{$id} was not found!");
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->errorBadRequest($e->getMessage());
        }
    }

    /**
     * Terbitkan BPPM atas PIBK ini
     */
    public function storeBppm(Request $r, $id) {
        DB::beginTransaction();
        try {
            // grab cd
            $pibk = PIBK::findOrFail($id);

            // spawn bppm
            $bppm = new BPPM([
                'kode_kantor' => $pibk->kode_kantor,
                'tgl_dok' => date('Y-m-d')
            ]);

            $bppm->payable()->associate($pibk);
            $bppm->pejabat()->associate(SSOUserCache::byId($r->userInfo['user_id']));

            // lock and save
            $bppm->lockAndSetNumber();

            // append status
            $pibk->appendStatus(
                'BPPM', 
                $r->get('lokasi') ?? $pibk->lokasi->nama ?? $pibk->lokasi->kode, 
                "Penerbitan Bukti Penerimaan Pembayaran Manual nomor {$bppm->nomor_lengkap}", 
                $bppm,
                null,
                SSOUserCache::byId($r->userInfo['user_id'])
            );

            // append log
            AppLog::logInfo(
                "Diterima pembayaran manual atas PIBK #{$id} dengan BPPM #{$bppm->id} oleh {$r->userInfo['username']}",
                $pibk,
                false
            );
            
            DB::commit();

            // return bppm id
            return $this->respondWithArray([
                'id' => $bppm->id,
                'uri' => $pibk->uri . '/bppm'
            ]);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return $this->errorNotFound("PIBK #{$id} was not found");
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->errorBadRequest($e->getMessage());
        }
    }
}
