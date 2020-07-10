<?php

namespace App\Http\Controllers;

use App\AppLog;
use App\CD;
use App\Lokasi;
use App\SPP;
use App\SSOUserCache;
use App\Transformers\SPPTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
    public function store(Request $r, $cdId)
    {
        DB::beginTransaction();

        try {
            // ambil data CD dulu
            $cd = CD::find($cdId);

            if (!$cd) {
                throw new NotFoundResourceException("CD #{$cdId} was not found");
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
                'kd_negara_asal' => substr($cd->kd_pelabuhan_asal,0,2)
            ]);

            // fill in the blanks
            $spp->cd()->associate($cd);
            $spp->lokasi()->associate($lokasi);
            $spp->pejabat()->associate(SSOUserCache::byId($r->userInfo['user_id']));
            
            // save and then log
            $spp->save();

            // log
            AppLog::logInfo("SPP #{$spp->id} diinput oleh {$r->userInfo['username']}", $spp);

            // add initial status for spp
            $spp->appendStatus('CREATED', $nama_lokasi, "CREATED FROM CD", $cd);

            // add new status for cd
            $cd->appendStatus('SPP', $nama_lokasi, "LOCKED BY SPP", $spp);

            // directly lock spp
            $spp->lockAndSetNumber('CREATED FROM CD');

            // lock cd too
            $cd->lockAndSetNumber('LOCKED BY SPP');

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
            return $this->errorNotFound("CD #{$cdId} was not found");
        } catch (\Exception $e) {
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
        if (!canEdit($spp->is_locked, $r->userInfo)) {
            return $this->errorForbidden("SPP sudah terkunci, dan privilege anda tidak cukup untuk operasi ini");
        }

        // attempt deletion
        try {
            AppLog::logWarning("SPP #{$id} dihapus oleh {$r->userInfo['username']}", $spp);

            $spp->delete();

            return $this->setStatusCode(204)
                        ->respondWithEmptyBody();
        } catch (\Exception $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }

    /**
     * generateMockup
     */
    public function generateMockup(Request $r, $cdId) {
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
            
            $spp->cd()->associate($cd);
            $spp->pejabat()->associate(SSOUserCache::byId($r->userInfo['user_id']));
            $spp->lokasi()->associate(Lokasi::byKode($r->get('lokasi'))->first() ?? $cd->lokasi);

            return $this->respondWithItem($spp, new SPPTransformer);
        } catch (NotFoundResourceException $e) {
            return $this->errorNotFound("CD #{$cdId} was not found");
        } catch (\Exception $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }
}
