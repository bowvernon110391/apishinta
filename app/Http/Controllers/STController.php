<?php

namespace App\Http\Controllers;

use App\AppLog;
use App\CD;
use App\Lokasi;
use App\SSOUserCache;
use App\ST;
use App\Transformers\STTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class STController extends ApiController
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
        $query = ST::byQuery(
            $r->get('q', ''),
            $r->get('from'),
            $r->get('to')
        )->when($jenis, function ($query) use ($jenis) {
            $query->jenis($jenis);
        });

        $paginator = $query
                    ->paginate($r->get('number'))
                    ->appends($r->except('page'));

        return $this->respondWithPagination($paginator, new STTransformer);
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
            $jenis      = expectSomething($r->get('jenis'), 'Jenis ST');

            // data lokasi
            $nama_lokasi    = expectSomething($r->get('lokasi'), "Lokasi Perekaman");
            $lokasi     = Lokasi::byKode($nama_lokasi)->first();

            // spawn a SPP from that cd
            $st = new ST([
                'tgl_dok' => date('Y-m-d'),
                'kd_negara_asal' => substr($cd->kd_pelabuhan_asal,0,2),
                'jenis' => $jenis
            ]);

            // fill in the blanks
            $st->cd()->associate($cd);
            $st->lokasi()->associate($lokasi);
            $st->pejabat()->associate(SSOUserCache::byId($r->userInfo['user_id']));
            
            // save and then log
            $st->save();

            // directly lock
            $st->lockAndSetNumber();
            $cd->lockAndSetNumber();

            // log
            AppLog::logInfo("ST #{$st->id} diinput oleh {$r->userInfo['username']}", $st);

            // add initial status for spp
            $st->appendStatus(
                'PENERBITAN', 
                $nama_lokasi, 
                "Penerbitan ST nomor {$st->nomor_lengkap} dari Customs Declaration nomor {$cd->nomor_lengkap}", 
                $cd,
                null,
                SSOUserCache::byId($r->userInfo['user_id'])
            );

            // add new status fir cd
            $cd->appendStatus(
                'ST', 
                $nama_lokasi, 
                "Dikunci dengan ST nomor {$st->nomor_lengkap}", 
                $st,
                null,
                SSOUserCache::byId($r->userInfo['user_id'])
            );

            // add keterangan to st
            $st->keterangan()->create([
                'keterangan' => $keterangan ?? '-'
            ]);

            // commit transaction
            DB::commit();

            // return something
            return $this->respondWithArray([
                'id'    => $st->id,
                'uri'   => '/st/' . $st->id
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
        $st = ST::find($id);

        if (!$st) {
            return $this->errorNotFound("ST #{$id} was not found!");
        }

        return $this->respondWithItem($st, new STTransformer);
    }

    public function showByCD($id) {
        $cd = CD::find($id);

        if (!$cd) {
            return $this->errorNotFound("CD #{$id} was not found");
        }

        // grab spp
        $st = $cd->st;

        if (!$st) {
            return $this->errorNotFound("CD #{$id} tidak memiliki relasi dengan ST manapun");
        }

        return $this->respondWithItem($st, new STTransformer);
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
        $st = ST::find($id);

        if (!$st) {
            return $this->errorNotFound("ST #{$id} was not found");
        }

        // are we authorized?
        if (!canEdit($st->is_locked, $r->userInfo)) {
            return $this->errorForbidden("ST sudah terkunci, dan privilege anda tidak cukup untuk operasi ini");
        }

        // attempt deletion
        try {
            AppLog::logWarning("ST #{$id} dihapus oleh {$r->userInfo['username']}", $st);

            $st->delete();

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
            $st = new ST([
                'tgl_dok' => date('Y-m-d'),
                'kd_negara_asal' => substr($cd->kd_pelabuhan_asal, 0, 2)
            ]);

            $st->cd()->associate($cd);
            $st->pejabat()->associate(SSOUserCache::byId($r->userInfo['user_id']));
            $st->lokasi()->associate(Lokasi::byKode($r->get('lokasi'))->first() ?? $cd->lokasi);

            return $this->respondWithItem($st, new STTransformer);
        } catch (NotFoundResourceException $e) {
            return $this->errorNotFound("CD #{$cdId} was not found");
        } catch (\Exception $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }
}
