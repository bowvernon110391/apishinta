<?php

namespace App\Http\Controllers;

use App\AppLog;
use App\CD;
use App\Kurs;
use App\Lokasi;
use App\PIBK;
use App\SPP;
use App\SSOUserCache;
use App\Transformers\SPPTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class SPPController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $r)
    {
        $jenis  = $r->get('jenis');
        // pure query?
        $query = SPP::byQuery(
            $r->get('q', ''),
            $r->get('from'),
            $r->get('to')
        );

        $paginator = $query
                    ->paginate($r->get('number'))
                    ->appends($r->except('page'));

        return $this->respondWithPagination($paginator, new SPPTransformer);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $r, $doctype, $id)
    {
        DB::beginTransaction();

        try {
            // ambil data dokumen dulu
            // $doc = $doctype == 'cd' ? CD::find($id) : PIBK::find($id);
            $doctype = strtoupper($doctype);
            $doc = null;
            $docTypename = '';

            switch($doctype) {
                case 'cd':
                case 'CD':
                    $doc = CD::find($id);
                    $docTypename = 'Customs Declaration';
                    break;
                case 'pibk':
                case 'PIBK':
                    $doc = PIBK::find($id);
                    $docTypename = 'PIBK';
                    break;
            }

            if (!$doc) {
                throw new NotFoundResourceException("{$doctype} #{$id} was not found");
            }

            // yang diperlukan hanya catatan,
            // dan lokasi, data pejabat, etc
            $keterangan = $r->get('keterangan', '');

            // data lokasi
            $nama_lokasi    = expectSomething($r->get('lokasi'), "Lokasi Perekaman");
            $lokasi     = Lokasi::byKode($nama_lokasi)->first();

            // spawn a SPP from that cd
            $spp = new SPP([
                'tgl_dok' => date('Y-m-d'),
                'kd_negara_asal' => substr($doc->kd_pelabuhan_asal,0,2)
            ]);

            // fill in the blanks
            $spp->source()->associate($doc);
            $spp->lokasi()->associate($lokasi);
            $spp->pejabat()->associate(SSOUserCache::byId($r->userInfo['user_id']));

            // save and then log
            $spp->save();

            // directly lock spp
            $spp->lockAndSetNumber("CREATED FROM {$doctype}");

            // lock doc too
            $doc->lockAndSetNumber('LOCKED BY SPP');

            // log
            AppLog::logInfo("SPP #{$spp->id} diinput oleh {$r->userInfo['username']}", $spp);

            // add initial status for spp
            $spp->appendStatus(
                'PENERBITAN',
                $nama_lokasi,
                "Penerbitan SPP nomor {$spp->nomor_lengkap} dari {$docTypename} nomor {$doc->nomor_lengkap}",
                $doc,
                null,
                SSOUserCache::byId($r->userInfo['user_id'])
            );

            // add new status for cd
            $doc->appendStatus(
                'SPP',
                $nama_lokasi,
                "Dikunci dengan SPP nomor {$spp->nomor_lengkap}",
                $spp,
                null,
                SSOUserCache::byId($r->userInfo['user_id'])
            );

            // add keterangan to spp
            $spp->keterangan()->create([
                'keterangan' => $keterangan ?? "-"
            ]);

            // commit transaction
            DB::commit();

            // return something
            return $this->respondWithArray([
                'id'    => $spp->id,
                'uri'   => '/spp/' . $spp->id
            ]);
        } catch (NotFoundResourceException $e) {
            DB::rollBack();
            return $this->errorNotFound("{$doctype} #{$id} was not found");
        } catch (\Exception $e) {
            Log::error("Error creating SPP from {$doctype}", ['exception' => $e]);
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
        $spp = SPP::find($id);

        if (!$spp) {
            return $this->errorNotFound("SPP #{$id} was not found!");
        }

        return $this->respondWithItem($spp, new SPPTransformer);
    }

    public function showByCD($id) {
        $cd = CD::find($id);

        if (!$cd) {
            return $this->errorNotFound("CD #{$id} was not found");
        }

        // grab spp
        $spp = $cd->spp;

        if (!$spp) {
            return $this->errorNotFound("CD #{$id} tidak memiliki relasi dengan SPP manapun");
        }

        return $this->respondWithItem($spp, new SPPTransformer);
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
        //
        $spp = SPP::find($id);

        if (!$spp) {
            return $this->errorNotFound("SPP #{$id} was not found");
        }

        // are we authorized?
        if (/* !canEdit($spp->is_locked, $r->userInfo) */!is_null($spp->pibk)) {
            return $this->errorForbidden("SPP sudah diselesaikan dengan PIBK");
        }

        DB::beginTransaction();
        // attempt deletion
        try {
            AppLog::logWarning("SPP #{$id} dihapus oleh {$r->userInfo['username']}", $spp, true);

            $cd = $spp->cd;
            $spp->delete();
            if ($cd) {
                $cd->restoreAndRefresh();
            }

            DB::commit();

            return $this->setStatusCode(204)
                        ->respondWithEmptyBody();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorBadRequest($e->getMessage());
        }
    }

    /**
     * generateMockup
     */
    public function generateMockupForCD(Request $r, $cdId) {
        // use try catch
        try {
            // make sure CD exists
            $cd = CD::find($cdId);

            if (!$cd) {
                throw new NotFoundResourceException("CD #{$cdId} was not found");
            }

            // generate mockup spp based on that
            $spp = new SPP([
                'tgl_dok' => date('Y-m-d'),
                'kd_negara_asal' => substr($cd->kd_pelabuhan_asal,0,2),
            ]);

            $spp->source()->associate($cd);
            $spp->pejabat()->associate(SSOUserCache::byId($r->userInfo['user_id']));
            $spp->lokasi()->associate(Lokasi::byKode($r->get('lokasi'))->first() ?? $cd->lokasi);

            return $this->respondWithItem($spp, new SPPTransformer);
        } catch (NotFoundResourceException $e) {
            return $this->errorNotFound("CD #{$cdId} was not found");
        } catch (\Exception $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }

    /**
     *
     */
    public function generateMockupForPIBK(Request $r, $pibkId) {
        try {
            $pibk = PIBK::find($pibkId);

            if (!$pibk) {
                throw new NotFoundResourceException("PIBK #{$pibkId} was not found");
            }

            // generate mockup spp based on that
            $spp = new SPP([
                'tgl_dok' => date('Y-m-d'),
                'kd_negara_asal' => substr($pibk->kd_pelabuhan_asal,0,2),
            ]);

            $spp->source()->associate($pibk);
            $spp->pejabat()->associate(SSOUserCache::byId($r->userInfo['user_id']));
            $spp->lokasi()->associate(Lokasi::byKode($r->get('lokasi'))->first() ?? $pibk->lokasi);

            return $this->respondWithItem($spp, new SPPTransformer);
        } catch (NotFoundResourceException $e) {
            return $this->errorNotFound("PIBK #{$pibkId} was not found");
        } catch (\Exception $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }

    /**
     * Create PIBK From this
     */
    public function storePIBK(Request $r, $id) {
        DB::beginTransaction();

        try {
            // first, grab the instance
            $s = SPP::findOrFail($id);

            // if it's not locked, throw error
            if (!$s->is_locked) {
                throw new \Exception("For some reason, this document is unlocked. Something's fishy....");
            }

            // if it already have pibk, return that instead (IDEMPOTENT RESPONSE)
            $p = $s->pibk;

            if ($p) {
                DB::rollBack();
                return $this->respondWithArray([
                    'id' => (int) $p->id,
                    'uri' => $p->uri
                ]);
            }

            $p = PIBK::createFromSource($r, $s);

            // commit
            DB::commit();

            // return info on the pibk?
            return $this->respondWithArray([
                'id' => (int) $p->id,
                'uri' => $p->uri
            ]);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return $this->errorNotFound("SPP #{$id} was not found");
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->errorBadRequest($e->getMessage());
        }
    }
}
