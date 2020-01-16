<?php

namespace App\Http\Controllers;

use App\AppLog;
use App\BPJ;
use App\Lokasi;
use App\Penumpang;
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
    public function store(Request $r)
    {
        // simpan BPJ
        // validasi dlu
        try {
            // pastikan json
            if (!$r->isJson()) {
                throw new \Exception("Data harus dalam bentuk JSON");
            }
            $tgl_dok = expectSomething($r->get('tgl_dok'), 'Tanggal BPJ');
            $lokasi = expectSomething($r->get('lokasi'), 'Lokasi');
            $penumpang_id = expectSomething($r->get('penumpang_id'), 'Id Penumpang');
            $jenis_identitas = expectSomething($r->get('jenis_identitas'), 'Jenis Identitas');
            $no_identitas = expectSomething($r->get('no_identitas'), 'Nomor Identitas');
            $alamat = expectSomething($r->get('alamat'),'Alamat');
            $nomor_jaminan = /* expectSomething( */$r->get('nomor_jaminan')/* ,'Nomor Jaminan') */;
            $tanggal_jaminan = expectSomething($r->get('tanggal_jaminan'),'Tanggal Jaminan');
            $penjamin = expectSomething($r->get('penjamin'),'Penjamin');
            $alamat_penjamin = expectSomething($r->get('alamat_penjamin'),'Alamat Penjamin');
            $bentuk_jaminan = expectSomething($r->get('bentuk_jaminan'),'Bentuk Jaminan');
            $jumlah = expectSomething($r->get('jumlah'),'Jumlah Jaminan');
            // $jenis = expectSomething($r->get('jenis'),'Jenis Jaminan');
            $tanggal_jatuh_tempo = expectSomething($r->get('tanggal_jatuh_tempo'),'Tanggal Jatuh Tempo');

            $nip_pembuat = expectSomething($r->userInfo['nip'], 'NIP Perekam BPJ');
            $nama_pembuat = expectSomething($r->userInfo['name'], 'Nama Perekam BPJ');

            $active = true;
            // $status = 'AKTIF';
            $catatan = $r->get('catatan');

            // apakah tunai?
            if ($bentuk_jaminan == 'TUNAI') {
                // force autonumbering
                $nomor_jaminan = getSequence('JT');
            } else if (!$nomor_jaminan) {
                // in any other case,
                // emptying it is a fail!!
                throw new \Exception("Nomor Jaminan tidak boleh kosong!");
            }

            // pastikan penumpang valid
            if (!Penumpang::find($penumpang_id)) {
                throw new \Exception("Penumpang dengan id {$penumpang_id} tidak ditemukan!");
            }

            $data = [
                'tgl_dok'           => $tgl_dok,
                'jenis_identitas'   => $jenis_identitas,
                'no_identitas'      => $no_identitas,
                'alamat'            => $alamat,
                'nomor_jaminan'     => $nomor_jaminan,
                'tanggal_jaminan'   => $tanggal_jaminan,
                'penjamin'          => $penjamin,
                'alamat_penjamin'   => $alamat_penjamin,
                'bentuk_jaminan'    => $bentuk_jaminan,
                'jumlah'            => $jumlah,
                // 'jenis'             => $jenis,
                'tanggal_jatuh_tempo'   => $tanggal_jatuh_tempo,
                'nip_pembuat'       => $nip_pembuat,
                'nama_pembuat'      => $nama_pembuat,
                'active'            => $active,
                // 'status'            => $status,
                'catatan'           => $catatan
            ];

            // return $this->respondWithArray($data);

            // spawn BPJ
            $b = new BPJ($data);

            // associate lokasi
            $b->lokasi()->associate(Lokasi::byName($lokasi)->first());

            // associate dengan penumpang
            $b->penumpang()->associate(Penumpang::find($penumpang_id));

            // $log = $b->toArray();
            // return $this->respondWithArray($log);

            // penomoran + save
            $b->setNomorDokumen();

            // log it
            AppLog::logInfo("BPJ #{$b->id} diinput oleh {$r->userInfo['username']}", $b);

            // update status
            $b->appendStatus('CREATED', $lokasi);

            // return with array
            return $this->respondWithArray([
                'id'    => $b->id,
                'uri'   => '/bpj/' . $b->id
            ]);
        } catch (\Exception $e) {
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
    public function update(Request $r, $id)
    {
        // update it
        try {
            // pastikan bpjnya ada
            $b = BPJ::findOrFail($id);
            // pastikan json
            if (!$r->isJson()) {
                throw new \Exception("Data harus dalam bentuk JSON");
            }

            $tgl_dok = expectSomething($r->get('tgl_dok'), 'Tanggal BPJ');
            $lokasi = expectSomething($r->get('lokasi'), 'Lokasi');
            $penumpang_id = expectSomething($r->get('penumpang_id'), 'Id Penumpang');
            $jenis_identitas = expectSomething($r->get('jenis_identitas'), 'Jenis Identitas');
            $no_identitas = expectSomething($r->get('no_identitas'), 'Nomor Identitas');
            $alamat = expectSomething($r->get('alamat'),'Alamat');
            $nomor_jaminan = /* expectSomething( */$r->get('nomor_jaminan')/* ,'Nomor Jaminan') */;
            $tanggal_jaminan = expectSomething($r->get('tanggal_jaminan'),'Tanggal Jaminan');
            $penjamin = expectSomething($r->get('penjamin'),'Penjamin');
            $alamat_penjamin = expectSomething($r->get('alamat_penjamin'),'Alamat Penjamin');
            $bentuk_jaminan = expectSomething($r->get('bentuk_jaminan'),'Bentuk Jaminan');
            $jumlah = expectSomething($r->get('jumlah'),'Jumlah Jaminan');
            // $jenis = expectSomething($r->get('jenis'),'Jenis Jaminan');
            $tanggal_jatuh_tempo = expectSomething($r->get('tanggal_jatuh_tempo'),'Tanggal Jatuh Tempo');

            // $nip_pembuat = expectSomething($r->userInfo['nip'], 'NIP Perekam BPJ');
            // $nama_pembuat = expectSomething($r->userInfo['name'], 'Nama Perekam BPJ');

            // $active = true;
            // $status = 'AKTIF';
            $catatan = $r->get('catatan');

            // pastikan penumpang valid
            if (!Penumpang::find($penumpang_id)) {
                throw new \Exception("Penumpang dengan id {$penumpang_id} tidak ditemukan!");
            }

            // apakah tunai? maybe it changes from etc into TUNAI?
            if ($bentuk_jaminan == 'TUNAI' && ($b->bentuk_jaminan !== $bentuk_jaminan) ) {
                // force autonumbering
                $nomor_jaminan = getSequence('JT');
            } else if (!$nomor_jaminan) {
                // in any other case,
                // emptying it is a fail!!
                throw new \Exception("Nomor Jaminan tidak boleh kosong!");
            }

            // spawn BPJ
            // $b = new BPJ($data);
            $b->tgl_dok         = $tgl_dok;
            $b->jenis_identitas = $jenis_identitas;
            $b->no_identitas    = $no_identitas;
            $b->alamat          = $alamat;
            $b->nomor_jaminan   = $nomor_jaminan;
            $b->tanggal_jaminan = $tanggal_jaminan;
            $b->penjamin        = $penjamin;
            $b->alamat_penjamin = $alamat_penjamin;
            $b->bentuk_jaminan  = $bentuk_jaminan;
            $b->jumlah          = $jumlah;
            // $b->jenis           = $jenis;
            $b->tanggal_jatuh_tempo = $tanggal_jatuh_tempo;
            $b->catatan         = $catatan;

            // $b->nip_pembuat     = $nip_pembuat;
            // $b->nama_pembuat    = $nama_pembuat;

            // associate lokasi
            $b->lokasi()->associate(Lokasi::byName($lokasi)->first());

            // associate dengan penumpang
            $b->penumpang()->associate(Penumpang::find($penumpang_id));

            // $log = $b->toArray();
            // return $this->respondWithArray($log);

            // penomoran + save
            // $b->setNomorDokumen();
            $b->save();

            // log it
            AppLog::logInfo("BPJ #{$b->id} diupdate oleh {$r->userInfo['username']}", $b);

            // update status
            // $b->appendStatus('UPDATED', $lokasi);

            // return with array
            return $this->setStatusCode(200)->respondWithEmptyBody();
        } catch (\Exception $e) {
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
        //  find it
        $bpj = BPJ::find($id);

        if (!$bpj) {
            return $this->errorNotFound("BPJ #{$id} tidak ditemukan.");
        }

        // forbid if user is unauthorized
        if (!canEdit($bpj->is_locked, $r->userInfo)) {
            return $this->errorForbidden("BPJ sudah terpakai, tidak dapat dibatalkan.");
        }

        // delete it
        // attempt deletion
        try {
            $bpjId = $bpj->id;

            AppLog::logWarning("BPJ #{$bpjId} dihapus oleh {$r->userInfo['username']}", $bpj);

            $bpj->delete();
            
            return $this->setStatusCode(204)
                        ->respondWithEmptyBody();
        } catch (\Exception $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }
}
