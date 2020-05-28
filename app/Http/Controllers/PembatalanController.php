<?php

namespace App\Http\Controllers;

use App\AppLog;
use App\Pembatalan;
use Illuminate\Http\Request;
use App\Transformers\PembatalanTransformer;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PembatalanController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $r)
    {
        // list semua dokumen pembatalan
        $query = Pembatalan::byQuery(
            $r->get('q', ''),
            $r->get('from'),
            $r->get('to')
        );

        $paginator = $query
                    ->paginate($r->get('number'))
                    ->appends($r->except('page'));

        return $this->respondWithPagination($paginator, new PembatalanTransformer);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $r)
    {
        // store our data
        try {
            DB::beginTransaction();

            if (!$r->isJson()) {
                throw new BadRequestHttpException("Request must be json");
            }

            // grab all data
            $data = $r->json()->all();

            $kode_kantor    = $data['kode_kantor'] ?? '050100';
            $no_dok      = $data['no_dok'] ?? 0;
            $nomor_lengkap_dok  = expectSomething($data['nomor_lengkap_dok'], "Nomor Lengkap Surat Pembatalan");
            $tgl_dok        = expectSomething($data['tgl_dok'], "Tanggal Surat Pembatalan");
            $nip_pejabat    = expectSomething($data['nip_pejabat'], "NIP Pejabat Pemberi Ijin Pembatalan");
            $nama_pejabat   = expectSomething($data['nama_pejabat'], "Nama Pejabat Pemberi Ijin Pembatalan");
            $keterangan     = expectSomething($data['keterangan'], "Alasan Pembatalan");

            // attempt to save
            $p = new Pembatalan([
                'kode_kantor'   => $kode_kantor,
                'no_dok'     => $no_dok,
                'nomor_lengkap_dok' => $nomor_lengkap_dok,
                'tgl_dok'       => $tgl_dok,
                'nip_pejabat'   => $nip_pejabat,
                'nama_pejabat'  => $nama_pejabat,
                'keterangan'    => $keterangan
            ]);
            
            // attempt to save
            $p->save();

            // append status
            $p->appendStatus('CREATED');

            // log it out?
            AppLog::logInfo("{$r->userInfo['username']} melakukan input data pembatalan #{$p->id}", $p, false);

            // commit?
            DB::commit();

            return $this->respondWithArray([
                'id'    => $p->id,
                'uri'   => '/pembatalan/' . $p->id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorBadRequest($e->getMessage());
        } catch (BadRequestHttpException $e) {
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
    public function show($id, Request $r)
    {
        // show full content of the data
        try {
            // try to get the data
            $p = Pembatalan::find($id);

            if (!$p) {
                throw new NotFoundHttpException("Pembatalan #{$id} was not found");
            }
            // simply show them?
            return $this->respondWithItem($p, new PembatalanTransformer);
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound($e->getMessage());
        } catch (\Exception $e) {
            return $this->errorBadRequest($e->getMessage());
        }
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
        // update data pembatalan
        try {
            DB::beginTransaction();
            // must be json
            if (!$r->isJson()) {
                throw new \Exception("Request must be json");
            }

            // find resource
            $p = Pembatalan::find($id);

            if (!$p) {
                throw new NotFoundHttpException("Pembatalan #{$id} was not found");
            }

            // make sure we can edit that shiet
            if (!canEdit($p->is_locked, $r->userInfo)) {
                throw new AccessDeniedHttpException("Insufficient privilege");
            }

            // log before save? rollbacked anyway if fail
            AppLog::logInfo("{$r->userInfo['username']} mengupdate Pembatalan #{$id}", $p);

            // read input
            $data = $r->json()->all();

            $p->kode_kantor     = $data['kode_kantor'] ?? $p->kode_kantor;
            $p->no_dok          = $data['no_dok'] ?? $p->no_dok;
            $p->nomor_lengkap_dok  = expectSomething($data['nomor_lengkap_dok'], "Nomor Lengkap Surat Pembatalan");
            $p->tgl_dok         = expectSomething($data['tgl_dok'], "Tanggal Surat Pembatalan");
            $p->nip_pejabat     = expectSomething($data['nip_pejabat'], "NIP Pejabat Pemberi Ijin Pembatalan");
            $p->nama_pejabat    = expectSomething($data['nama_pejabat'], "Nama Pejabat Pemberi Ijin Pembatalan");
            $p->keterangan      = expectSomething($data['keterangan'], "Alasan Pembatalan");

            // save
            $p->save();

            // commit
            DB::commit();

            // return 204
            return $this->setStatusCode(204)
                        ->respondWithEmptyBody();
        } catch (AccessDeniedHttpException $e) {
            DB::rollBack();
            return $this->errorForbidden($e->getMessage());
        } catch (NotFoundHttpException $e) {
            DB::rollBack();
            return $this->errorNotFound($e->getMessage());
        } catch (\Exception $e) {
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
    public function destroy($id)
    {
        //
    }
}
