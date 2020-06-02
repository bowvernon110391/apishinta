<?php

namespace App\Http\Controllers;

use App\AppLog;
use App\BPJ;
use App\Cancellable;
use App\CD;
use App\IS;
use App\Pembatalan;
use App\SPP;
use App\ST;
use Illuminate\Http\Request;
use App\Transformers\PembatalanTransformer;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PembatalanController extends ApiController
{
    /**
     * Display a listing of Surat Pembatalan.
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

        // perhaps it's only grab the unlocked ones?
        if (($status = $r->get('status'))) {
            $query = $query->byStatus($status);
        }

        $paginator = $query
                    ->paginate($r->get('number'))
                    ->appends($r->except('page'));

        return $this->respondWithPagination($paginator, new PembatalanTransformer);
    }

    /**
     * Store a newly created Surat Pembatalan in storage.
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
            $nomor_lengkap_dok  = expectSomething($data['nomor_lengkap'], "Nomor Lengkap Surat Pembatalan");
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
     * Display the specified Surat Pembatalan.
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
     * Update the specified Surat Pembatalan in storage.
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
            $p->nomor_lengkap_dok  = expectSomething($data['nomor_lengkap'], "Nomor Lengkap Surat Pembatalan");
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
     * Remove the specified Surat Pembatalan from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $r, $id)
    {
        // delete the data itself
        try {
            DB::beginTransaction();

            // find it
            $p = Pembatalan::find($id);

            if (!$p) {
                throw new NotFoundHttpException("Pembatalan #{$id} was not found");
            }

            // do we have sufficient privilege?
            // fail when: it's locked already AND user is not CONSOLE
            if ($p->is_locked && !userHasRole('CONSOLE', $r->userInfo)) {
                throw new AccessDeniedHttpException("Insufficient privilege");
            }

            // log it first
            AppLog::logWarning("{$r->userInfo['username']} menghapus Pembatalan #{$id}", $p, true);

            // do the deletion
            $p->delete();

            // commit it
            DB::commit();

            // return empty response
            return $this->setStatusCode(204)
                        ->respondWithEmptyBody();
        } catch (NotFoundHttpException $e) {
            DB::rollBack();
            return $this->errorNotFound($e->getMessage());
        } catch (AccessDeniedHttpException $e) {
            DB::rollBack();
            return $this->errorForbidden($e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorBadRequest($e->getMessage());
        }
    }

    /**
     * Kunci dokumen pembatalan
     */
    public function lockPembatalan(Request $r, $id) {
        try {
            DB::beginTransaction();

            $p = Pembatalan::find($id);

            if (!$p) {
                throw new NotFoundHttpException("Pembatalan #{$id} was not found");
            }

            // it's there, let's lock it
            // locking is idempotent. should always return true
            // unless something happened
            if (!$p->lock()) {
                throw new \Exception("Something weird really happened. Dunno what though");
            }

            // success! log it
            AppLog::logInfo("{$r->userInfo['username']} locked Pembatalan #{$id}", $p, false);

            DB::commit();

            // success? return 204
            return $this->setStatusCode(204)
                        ->respondWithEmptyBody();
        } catch (NotFoundHttpException $e) {
            DB::rollBack();
            return $this->errorNotFound($e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorBadRequest($e->getMessage());
        }
    }

    /**
     * Tambah dokumen untuk dibatalkan
     */
    public function addDokumen(Request $r, $id, $doctype, $docid) {
        try {
            DB::beginTransaction();

            // check if we found it?
            $p = Pembatalan::find($id);

            if (!$p) {
                throw new NotFoundHttpException("Pembatalan #{$id} was not found");
            }

            // we found it. but is it locked?
            if ($p->is_locked) {
                throw new BadRequestHttpException("Pembatalan #{$id} was already locked. Unlock it first (if you can)");
            }

            // is it supported though?
            $doc = Pembatalan::instantiate($doctype, $docid);

            if (!$doc) {
                throw new BadRequestHttpException("Either {$doctype} #{$docid} does not exist or it was not supported for cancellation");
            }

            // it was supported. Steps for cancellation
            // 1. mark it for cancellation
            $doc->pembatalan()->syncWithoutDetaching($p);
            // 2. delete it
            // 3. log it before deleting though
            AppLog::logWarning("{$r->userInfo['username']} delete {$doctype} #{$docid}", $doc, true);
            AppLog::logWarning("{$r->userInfo['username']} delete {$doctype} #{$docid} using Pembatalan #{$id}", $p, false);

            // delete it
            $doc->delete();

            DB::commit();

            // return 204? because pivot data can't be queried 
            // precisely
            return $this->setStatusCode(204)
                        ->respondWithEmptyBody();
        } catch (NotFoundHttpException $e) {
            DB::rollBack();
            return $this->errorNotFound($e->getMessage());
        } catch (BadRequestHttpException $e) {
            DB::rollBack();
            return $this->errorBadRequest($e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorBadRequest($e->getMessage());
        }
    }

    /**
     * Hapus detail pembatalan dokumen (batalkan pembatalan)
     */
    public function delDokumen(Request $r, $id) {
        try {
            DB::beginTransaction();

            // check if we found it?
            $d = Cancellable::find($id);

            if (!$d) {
                // detail not found
                throw new NotFoundHttpException("Detail Pembatalan #{$id} was not found");
            }

            // grab instance of surat pembatalan
            $p = $d->header();

            if (!$p) {
                throw new NotFoundHttpException("Pembatalan #{$id} was not found");
            }

            // we found it. but is it locked?
            if ($p->is_locked) {
                throw new BadRequestHttpException("Pembatalan #{$id} was already locked. Unlock it first (if you can)");
            }

            // is it supported though?
            $doc = $d->instance;

            if (!$doc) {
                throw new BadRequestHttpException("The deleted document seems to not be restorable :(");
            }

            // it was supported. Steps for cancelling cancellation
            // 1. restore it
            $doc->restore();

            // 2. unmark it
            $doc->pembatalan()->sync([]);

            // 3. log it
            AppLog::logWarning("{$r->userInfo['username']} restore the deleted document {get_class($doc)} #{$doc->id}", $doc, false);
            AppLog::logWarning("{$r->userInfo['username']} cancel the deletion of Detail Pembatalan {$id}", $p, false);

            DB::commit();

            // return 204
            return $this->setStatusCode(204)
                        ->respondWithEmptyBody();
        } catch (NotFoundHttpException $e) {
            DB::rollBack();
            return $this->errorNotFound($e->getMessage());
        } catch (BadRequestHttpException $e) {
            DB::rollBack();
            return $this->errorBadRequest($e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorBadRequest($e->getMessage());
        }
    }
}

