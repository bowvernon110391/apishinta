<?php

namespace App\Http\Controllers;

use App\PIBK;
use App\Transformers\DetailBarangTransformer;
use App\Transformers\PIBKTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class PIBKController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $r)
    {
        // grab a query and return shiets
        $q = $r->get('q', '');
        $from = $r->get('from');
        $to = $r->get('to');

        $query = PIBK::byQuery($q, $from, $to);

        $paginator = $query->paginate($r->get('number'))
                            ->appends($r->except('page'));

        return $this->respondWithPagination($paginator, new PIBKTransformer);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // show one PIBK
        try {
            $p = PIBK::findOrFail($id);

            return $this->respondWithItem($p, new PIBKTransformer);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound("PIBK #{$id} was not found");
        } catch (\Throwable $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }

    /**
     * Show detail barang of a pibk
     */
    public function showDetails(Request $r, $id) {
        $pibk = PIBK::find($id);

        if (!$pibk) {
            return $this->errorNotFound("PIBK #{$id} was not found");
        }

        // grab details
        $paginator = $pibk->detailBarang()/* ->isPenetapan() */
                    ->paginate($r->get('number', 10))
                    ->appends($r->except('page'));
        
        return $this->respondWithPagination($paginator, new DetailBarangTransformer);
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
        // update stuffs. to do later
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $r, $id)
    {
        // show one PIBK
        try {
            $p = PIBK::findOrFail($id);

            if (!canEdit($p->is_locked, $r->userInfo)) {
                throw new \Exception("PIBK #{$id} is locked and you don't have enough privilege to bypass it");
            }

            $p->delete();

            return $this->setStatusCode(204)
                    ->respondWithEmptyBody();
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound("PIBK #{$id} was not found");
        } catch (\Throwable $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }
}
