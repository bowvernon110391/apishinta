<?php

namespace App\Http\Controllers;

use App\AppLog;
use App\InstruksiPemeriksaan;
use App\LHP;
use App\Lokasi;
use App\Services\LHPManager;
use App\SSOUserCache;
use App\Transformers\LHPTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use PhpParser\Node\Expr\AssignOp\Mod;

class LHPController extends ApiController
{
    // inject lhp manager
    public function __construct(Manager $m, Request $r, LHPManager $lhpManager)
    {
        parent::__construct($m, $r);
        $this->lhpManager = $lhpManager;
    }
    // show lhp?
    public function showLHP(Request $r, $id) {
        try {
            $l = LHP::findOrFail($id);

            return $this->respondWithItem($l, new LHPTransformer);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound("LHP #{$id} was not found");
        } catch (\Exception $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }

    // show based on route?
    public function showResolvedLHP(Request $r) {
        try {
            $lhp = $this->lhpManager->findOrFail($r->path());

            // if locked_only flag is set, throw some error if this document is not locked
            if ($r->get('locked_only', false) == true) {
                if (!$lhp->is_locked) {
                    throw new \Exception("LHP #{$lhp->id} belum selesai direkam!");
                }
            }

            return $this->respondWithItem($lhp, new LHPTransformer);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound("LHP atas uri ini tidak ditemukan");
        } catch (\Exception $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }

    // update based on route?
    public function updateResolvedLHP(Request $r) {
        try {
            // should we bother? YUP
            $l = $this->lhpManager->findOrFail($r->path());

            // perhaps force failure if it's locked
            if ($l->is_locked) {
                throw new \Exception("LHP #{$l->id} sudah direkam dan terkunci!");
            }

            // simply update...
            $l->tgl_dok = $r->get('tgl_dok', date('Y-m-d'));
            $l->pemeriksa_id = $r->get('pemeriksa_id', $r->userInfo['user_id']);
            $l->lokasi()->associate(Lokasi::byKode($r->get('lokasi', 'KANTOR'))->first());
            $l->isi = $r->get('isi', '');

            SSOUserCache::byId($r->userInfo['user_id']);

            $l->save();
            // log
            AppLog::logInfo("LHP #{$l->id} updated by {$r->userInfo['username']}", $l, true);

            // if locking is requested, lock it
            if ($r->get('lock') == true) {
                // lock it
                $l->lockAndSetNumber();

                // lock parent too?
                if ($l->inspectable) {
                    // Append status first to parent
                    $l->inspectable->appendStatus(
                        'LHP', 
                        null, 
                        "LHP telah direkam", 
                        $l, 
                        null,
                        SSOUserCache::byId($r->userInfo['user_id'])
                    );
                    if (get_class($l->inspectable) == InstruksiPemeriksaan::class) {
                        // lock it! (CAUSE IP IS LOCKED BY LHP)
                        $l->inspectable->lockAndSetNumber();
                        // also append to parent, the "REAL" document
                        $l->inspectable->instructable->appendStatus(
                            'LHP', 
                            null, 
                            "LHP telah direkam", 
                            $l, 
                            null,
                            SSOUserCache::byId($r->userInfo['user_id'])
                        );
                        
                        // append log too?
                        AppLog::logInfo("IP #{$l->inspectable->id} was closed by LHP #{$l->id}", $l->inspectable, false);
                    }
                }

                // append log?
                AppLog::logInfo("LHP #{$l->id} was finished by {$r->userInfo['username']}", $l, false);
            }
            
            return $this->respondWithArray([
                'id' => $l->id,
                'uri' => $l->uri
            ]);

        } catch (ModelNotFoundException $e) {
            // not found, try to spawn first
            return $this->firstOrNew($r);
        } catch (\Exception $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }

    public function firstOrNew(Request $r) {
        $l = null;
        try {
            //code...
            $l = $this->lhpManager->findOrFail($r->path());
        } catch (\Throwable $th) {
        }

        try {
            
            // if not found, spawn anew?
            if (!$l) {
                // throw new \Exception("Do I Reach here");

                $parent = $this->lhpManager->resolveParent($r->path());

                // maybe parent cannot be resolved
                if (!$parent) {
                    throw new \Exception("Parent document cannot be resolved...");
                }

                // if parent exist, let's spawn new lhp and add it
                if (!method_exists($parent, 'instruksiPemeriksaan')) {
                    throw new \Exception(get_class($parent) . " cannot have LHP!");
                }

                // does it have instruksi pemeriksaan yet?
                if (!$parent->instruksiPemeriksaan) {
                    throw new \Exception("Instruksi pemeriksaan atas dokumen ini belum diterbitkan!");
                }

                // let's create default LHP
                $l = $parent->instruksiPemeriksaan->lhp()->firstOrNew([
                    'isi' => $r->get('isi', '')
                    ]);

                // add rest of data
                $l->tgl_dok = $r->get('tgl_dok', date('Y-m-d'));    // automatically set date to current
                $l->pemeriksa_id = $r->userInfo['user_id']; // automatically associate to current token owner
                $l->lokasi()->associate(Lokasi::byKode($r->get('lokasi', 'KANTOR'))->first());  // failsafe to KANTOR

                // cache sso user (the pemeriksa)
                SSOUserCache::byId($r->userInfo['user_id']);

                // save and add status
                $l->save();
                $l->appendStatus(
                    'CREATED', 
                    null, 
                    'mulai diperiksa oleh ' . $r->userInfo['username'], 
                    $parent,
                    null,
                    SSOUserCache::byId($r->userInfo['user_id'])
                );
                // also add number?
                $l->setNomorDokumen();
            } else {
                // touch it maybe?
                $l->touch();
            }

            // just return its id and uri?
            return $this->respondWithArray([
                'id' => $l->id,
                'uri' => $l->uri
            ]);
        } catch (\Exception $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }
}
