<?php

namespace App\Http\Controllers;

use App\LHP;
use App\Services\LHPManager;
use App\Transformers\LHPTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use League\Fractal\Manager;

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

            return $this->respondWithItem($lhp, new LHPTransformer);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound($e->getMessage());
        } catch (\Exception $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }

    public function putLHP(Request $r) {
        $l = $this->lhpManager->findOrFail($r);

        return $l;
    }
}
