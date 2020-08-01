<?php

namespace App\Http\Controllers;

use App\AppLog;
use App\BPJ;
use App\BPPM;
use Illuminate\Http\Request;
use App\CD;
use App\DeclareFlag;
use App\Keterangan;
use App\Kurs;
use App\Lock;
use App\Lokasi;
use App\Penumpang;
use App\Pungutan;
use App\ReferensiJenisPungutan;
use App\SPPB;
use App\SSOUserCache;
use App\SSPCP;
use App\Transformers\CDTransformer;
use App\Transformers\DetailBarangTransformer;
use App\Transformers\DetailCDTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class CDController extends ApiController
{
    /**
     * Display a listing of Customs Declaration, possibly with query strings for custom query
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = CD::pure()->byQuery(
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
        DB::beginTransaction();
        // validasi dlu
        try {
            // tgl dok
            $tgl_dok = expectSomething($r->get('tgl_dok'), 'Tanggal Dokumen');
            $lokasi = expectSomething($r->get('lokasi'), 'Lokasi');
            $penumpang_id = expectSomething($r->get('penumpang_id'), 'Id Penumpang');
            $npwp_nib = $r->get('npwp_nib') ?? '';
            $no_flight = expectSomething($r->get('no_flight'), 'Nomor flight');
            $kd_airline = $r->get('kd_airline');
            $tgl_kedatangan = expectSomething($r->get('tgl_kedatangan'), 'Tanggal Kedatangan');
            $kd_pelabuhan_asal = expectSomething($r->get('kd_pelabuhan_asal'), 'Kode Pelabuhan Asal');
            $kd_pelabuhan_tujuan = expectSomething($r->get('kd_pelabuhan_tujuan'), 'Kode Pelabuhan Tujuan');
            $alamat = expectSomething($r->get('alamat'), 'Alamat Tinggal');
            $declare_flags  = $r->get('declare_flags');

            // koli
            $koli = expectSomething($r->get('koli'), 'Koli Barang');

            // keluarga, pembebasan
            $jml_bagasi_dibawa = expectSomething($r->get('jml_bagasi_dibawa'), 'Jumlah Bagasi Dibawa');
            $jml_bagasi_tdk_dibawa = expectSomething($r->get('jml_bagasi_tdk_dibawa'), 'Jumlah Bagasi Tidak Dibawa');
            $pembebasan = expectSomething($r->get('pembebasan'), 'Jumlah Pembebasan');
            $jml_anggota_keluarga = expectSomething($r->get('jml_anggota_keluarga'), 'Jumlah Anggota Keluarga');
            $pph_tarif = expectSomething($r->get('pph_tarif'), 'Tarif PPh');
            // $ndpbm = expectSomething($r->get('ndpbm'), 'NDPBM');

            // pastikan id penumpang valid
            if (!Penumpang::find($penumpang_id)) {
                throw new \Exception("Penumpang dengan id {$penumpang_id} tidak ditemukan!");
            }

            // force kode airline
            if (strlen($kd_airline) < 2 && strlen($no_flight) < 5) {
                throw new \Exception("Data penerbangan tidak valid. Cek kembali nomor flightnya");
            } else if (strlen($kd_airline) < 2) {
                $kd_airline = strtoupper(substr($no_flight, 0, 2));
            }

            $cd = new CD([
                'tgl_dok'   => $tgl_dok,
                'penumpang_id'    => $penumpang_id,
                'no_flight'    => $no_flight,
                'kd_airline'    => $kd_airline,
                'tgl_kedatangan'    => $tgl_kedatangan,
                'kd_pelabuhan_asal'    => $kd_pelabuhan_asal,
                'kd_pelabuhan_tujuan'    => $kd_pelabuhan_tujuan,
                'alamat'    => $alamat,
                'jml_bagasi_dibawa'     => $jml_bagasi_dibawa,
                'jml_bagasi_tdk_dibawa' => $jml_bagasi_tdk_dibawa,
                'pembebasan'    => $pembebasan,
                'jml_anggota_keluarga'  => $jml_anggota_keluarga,
                'pph_tarif'     => $pph_tarif,
                'koli'      => $koli
            ]);

            // set npwp/nib
            if (/* $npwp_nib */true) {
                $cd->npwp = $cd->nib = $npwp_nib;
            }

            // associate lokasi first
            $cd->lokasi()->associate(Lokasi::byKode($lokasi)->first());

            // ndpbm
            $kursNdpbm = Kurs::perTanggal(date('Y-m-d'))->kode('USD')->first(); //Kurs::find($ndpbm['data']['id']);

            // if not found, refuse further processing
            if (!$kursNdpbm) {
                throw new \Exception("Kurs NDPBM (USD) invalid. Pastikan data kurs terupdate");
            }

            $cd->ndpbm()->associate($kursNdpbm);

            // try save first
            $cd->save();

            // sync flags and lokasi
            $cd->declareFlags()->sync(DeclareFlag::byName($declare_flags)->get());

            // penomoran
            // $cd->setNomorDokumen();  // diset nanti pas penetapan

            // log it
            AppLog::logInfo("CD #{$cd->id} diinput oleh {$r->userInfo['username']}", $cd, false);

            // Update status
            $cd->appendStatus('CREATED', $lokasi);

            // validasi
            $cd->validate();

            // commit
            DB::commit();

            // return with array
            return $this->respondWithArray([
                'id'    => $cd->id,
                'uri'   => '/cd/' . $cd->id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
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
        $paginator = $cd->detailbarang()->isPenetapan()
                    ->paginate($request->get('number', 10))
                    ->appends($request->except('page'));

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
        DB::beginTransaction();
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
            $kd_airline = $r->get('kd_airline');
            $cd->tgl_kedatangan = expectSomething($r->get('tgl_kedatangan'), 'Tanggal Kedatangan');
            $cd->kd_pelabuhan_asal = expectSomething($r->get('kd_pelabuhan_asal'), 'Kode Pelabuhan Asal');
            $cd->kd_pelabuhan_tujuan = expectSomething($r->get('kd_pelabuhan_tujuan'), 'Kode Pelabuhan Tujuan');
            $cd->alamat = expectSomething($r->get('alamat'), 'Alamat/Domisili');

            // koli
            $cd->koli = expectSomething($r->get('koli'), 'Koli Barang');

            // keluarga, pembebasan
            $cd->jml_bagasi_dibawa = expectSomething($r->get('jml_bagasi_dibawa'), 'Jumlah Bagasi Dibawa');
            $cd->jml_bagasi_tdk_dibawa = expectSomething($r->get('jml_bagasi_tdk_dibawa'), 'Jumlah Bagasi Tidak Dibawa');
            $cd->pembebasan = expectSomething($r->get('pembebasan'), 'Jumlah Pembebasan');
            $cd->jml_anggota_keluarga = expectSomething($r->get('jml_anggota_keluarga'), 'Jumlah Anggota Keluarga');
            $cd->pph_tarif = expectSomething($r->get('pph_tarif'), 'Tarif PPh');
            
            $declare_flags  = $r->get('declare_flags');
            $lokasi = expectSomething($r->get('lokasi'), 'Lokasi');
            $npwp_nib = $r->get('npwp_nib') ?? '';
            // $ndpbm = expectSomething($r->get('ndpbm'), 'NDPBM');

            // set npwp/nib
            if (/* $npwp_nib */true) {
                $cd->npwp = $cd->nib = $npwp_nib;
            }

            // pastikan id penumpang valid
            if (!Penumpang::find($cd->penumpang_id)) {
                throw new \Exception("Penumpang dengan id {$cd->penumpang_id} tidak ditemukan!");
            }

            // force kode airline
            if (strlen($kd_airline) < 2 && strlen($cd->no_flight) < 5) {
                throw new \Exception("Data penerbangan tidak valid. Cek kembali nomor flightnya");
            } else if (strlen($kd_airline) < 2) {
                $kd_airline = strtoupper(substr($cd->no_flight, 0, 2));
            }

            $cd->kd_airline = $kd_airline;
            // $cd->ndpbm_id = $ndpbm['data']['id'];

            // ndpbm
            $kursNdpbm = Kurs::perTanggal(date('Y-m-d'))->kode('USD')->first(); //Kurs::find($ndpbm['data']['id']);

            // if not found, refuse further processing
            if (!$kursNdpbm) {
                throw new \Exception("Kurs NDPBM invalid. Pastikan data kurs up-to-date");
            }

            $cd->ndpbm()->associate($kursNdpbm);
            $cd->lokasi()->associate(Lokasi::byKode($lokasi)->first());
            $cd->declareFlags()->sync(DeclareFlag::byName($declare_flags)->get());
            
            // try to save
            $cd->save();
            
            // sync data dokkap
            $cd->syncDokkap($r->get('dokkap')['data']);

            // log it
            AppLog::logInfo("CD #{$cd->id} diupdate oleh {$r->userInfo['username']}", $cd, false);

            // validate
            $cd->validate();

            // commit
            DB::commit();

            // return no body
            return $this->setStatusCode(204)->respondWithEmptyBody();
        } catch (\Exception $e) {
            DB::rollBack();
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
            $cdId = $cd->id;

            AppLog::logWarning("CD #{$id} dihapus oleh {$r->userInfo['username']}", $cd, true);

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
            $pungutan = $cd->komersil ? $cd->computePungutanCdKomersil() : $cd->computePungutanCdPersonal();
            $keterangan = $cd->keterangan()->first();

            return $this->respondWithArray([
                'pungutan' => $pungutan,
                'keterangan' => $keterangan
            ]);
        } catch (\Exception $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }

    /**
     * Penetapan cd
     */
    public function storePenetapan(Request $r, $id) {
        // USE TRANSACTION!!!! CAUSE IT INVOLVES MORE THAN ONE TABLE
        DB::beginTransaction();
        try {
            // grab cd
            $cd = CD::findOrFail($id);

            // gotta check if cd is locked?
            if ($cd->is_locked) {
                throw new \Exception("CD sudah terkunci!");
            }

            // gotta check if cd is komersil
            if ($cd->komersil) {
                throw new \Exception("Penetapan CD non personal use harap gunakan dokumen lain (SPP/ST/PIBK/etc...)");
            }

            // okay, grab computed data
            $data = $cd->computePungutanCdPersonal();

            // grab pungutan
            $pungutan = $data['pungutan'];

            if (!count($pungutan)) {
                throw new \Exception("Tidak ada pungutan untuk CD ini! cek kembali datanya");
            }

            // #1st, SPAWN PUNGUTAN
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
                $p->dutiable()->associate($cd);
                $p->jenisPungutan()->associate($refJenis);

                $p->save();
            }

            // #2, KETERANGAN
            $cd->keterangan()->create([
                'keterangan' => $r->get('keterangan', '') ?? ''
            ]);

            // #3, LOCK
            $l = new Lock([
                'keterangan' => "Penetapan CD Personal"
            ]);
            
            $l->petugas()->associate(SSOUserCache::byId($r->userInfo['user_id']));
            $cd->lock()->save($l);

            // #4, APPEND STATUS
            $cd->appendStatus(
                'PENETAPAN',
                null,
                "Penetapan Pungutan atas CD",
                null,
                null,
                $l->petugas
            );

            // #5, LOG IT
            AppLog::logInfo("CD #{$id} ditetapkan pungutannya oleh {$r->userInfo['username']}", $cd, false);

            // #6, SET NOMOR DOKUMEN
            $cd->setNomorDokumen();

            // commit
            DB::commit();

            // if success, just return 204
            return $this->setStatusCode(204)
                        ->respondWithEmptyBody();
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return $this->errorNotFound("CD #{$id} was not found!");
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->errorBadRequest($e->getMessage());
        }
    }

    /**
     * Terbitkan BPPM atas CD ini
     */
    public function storeBppm(Request $r, $id) {
        DB::beginTransaction();
        try {
            // grab cd
            $cd = CD::findOrFail($id);

            // spawn bppm
            $bppm = new BPPM([
                'kode_kantor' => $cd->kode_kantor,
                'tgl_dok' => date('Y-m-d')
            ]);

            $bppm->payable()->associate($cd);
            $bppm->pejabat()->associate(SSOUserCache::byId($r->userInfo['user_id']));

            // lock and save
            $bppm->lockAndSetNumber();

            // append status
            $cd->appendStatus(
                'BPPM', 
                $r->get('lokasi') ?? $cd->lokasi->nama, 
                "Penerbitan Bukti Penerimaan Pembayaran Manual nomor {$bppm->nomor_lengkap}", 
                $bppm,
                null,
                SSOUserCache::byId($r->userInfo['user_id'])
            );

            // append log
            AppLog::logInfo(
                "Diterima pembayaran manual atas CD #{$id} dengan BPPM #{$bppm->id} oleh {$r->userInfo['username']}",
                $cd,
                false
            );
            
            DB::commit();

            // return bppm id
            return $this->respondWithArray([
                'id' => $bppm->id,
                'uri' => $cd->uri . '/bppm'
            ]);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return $this->errorNotFound("CD #{$id} was not found");
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->errorBadRequest($e->getMessage());
        }
    }

    /**
     * Terbitkan SPPB atas CD ini
     */
    public function storeSppb(Request $r, $id) {
        DB::beginTransaction();

        try {
            //code...
            $cd = CD::findOrFail($id);

            // gotta grab sppb data, in this case?

            // #1, grab lokasi from that data
            $lokasi = $cd->lokasi;

            if (!$lokasi) {
                throw new \Exception("Lokasi CD tidak boleh kosong!");
            }

            // #2, spawn sppb and associate it
            $s = new SPPB([
                'kode_kantor' => '050100',
                'tgl_dok' => date('Y-m-d')
            ]);

            $s->gateable()->associate($cd);
            $s->lokasi()->associate($lokasi);
            $s->pejabat()->associate(SSOUserCache::byId($r->userInfo['user_id']));

            // #3 save and lock sppb
            $s->lockAndSetNumber();

            // #4 append status CD
            $cd->appendStatus(
                'SPPB', 
                null, 
                "Penerbitan SPPB nomor {$s->nomor_lengkap_dok}", 
                $s,
                null,
                SSOUserCache::byId($r->userInfo['user_id'])
            );

            // #5 log it
            AppLog::logInfo("CD #{$id} diterbitkan SPPB #{$s->id} oleh {$r->userInfo['username']}", $cd, false);

            // commit
            DB::commit();

            // return empty
            return $this->setStatusCode(204)
                        ->respondWithEmptyBody();
            
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return $this->errorNotFound("CD #{$id} was not found");
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->errorBadRequest($e->getMessage());
        }
    }

    /**
     * store billing data to cd
     */
    public function storeBilling(Request $r, $id) {
        DB::beginTransaction();
        try {
            // grab cd
            $cd = CD::findOrFail($id);

            // store some new billing data
            $b = $cd->billing()->create([
                'nomor' => expectSomething($r->get('nomor'), 'Nomor Billing'),
                'tanggal' => expectSomething($r->get('tanggal'), 'Tanggal Billing'),

                // the rest of the data can be null (empty)
                'ntb' => $r->get('ntb'),
                'ntpn' => $r->get('ntpn'),
                'tgl_ntpn' => $r->get('tgl_ntpn')
            ]);

            // append status
            $cd->appendStatus(
                'BILLING',
                null,
                "Perekaman Data billing nomor {$b->nomor}",
                $b,
                null,
                SSOUserCache::byId($r->userInfo['user_id'])
            );

            // commit
            DB::commit();

            // return empty (Billing data is not important)
            return $this->setStatusCode(204)
                        ->respondWithEmptyBody();
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return $this->errorNotFound("CD #{$id} was not found");
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->errorBadRequest($e->getMessage());
        }
    }
}
