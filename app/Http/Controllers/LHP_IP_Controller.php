<?php

namespace App\Http\Controllers;

use App\LHP;
use App\Transformers\LHPTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class LHP_IP_Controller extends ApiController
{
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
}
